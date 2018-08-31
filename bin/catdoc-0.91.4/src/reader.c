
#include <string.h>
#include <stdio.h>
#include "catdoc.h"
static unsigned short int buffer[PARAGRAPH_BUFFER];
static unsigned char read_buf[256];
static int buf_is_unicode;
/**************************************************************************/
/* Just prints out content of input file. Called when file is not OLE     */
/* stream                                                                 */
/**************************************************************************/
void copy_out (FILE *f,char *header) {
    char *buf=(char *)buffer;
    int count,i;
    long offset;
    if (get_unicode_char == get_word8_char) {
       /* non-word file and -u specified. Trying to guess which kind of
	* unicode is used
	*/
	if ((unsigned char)header[0]==0xFE && (unsigned char)header[1]==0xFF) {
	     get_unicode_char = get_utf16msb;
	     fputs(convert_char(header[2]<<8|header[3]),stdout); 
	     fputs(convert_char(header[4]<<8|header[5]),stdout); 
	     fputs(convert_char(header[6]<<8|header[7]),stdout); 
	} else if ((unsigned char)header[0]!=0xFF ||
		(unsigned char)header[1]!=0xFE) {
	    int c,j,d;
	    /* if it is not utf16, assume it is UTF8. We are told -u,
	     * aren't we */
	    get_unicode_char = get_utf8;
	    i=0;
	    while (i<8) {
		c=(unsigned char)header[i++];		
		if (c >=0x80) {
		if ( c<0xE0) {
		    c=(c & 0x1F);
		    count =1;
		} else {
		    c=(c & 0xF);
		    count = 2;
		}
                for (j=0;j<count;j++) {
		   if (i<7) {
		       d=(unsigned char) header[i++];
		   } else {
		       d=fgetc(f);
		   }
		   c=c<<6 | (d & 0x3F);
		}
		}
		fputs (convert_char(c),stdout);
	    }
	} else {
	     get_unicode_char = get_utf16lsb;
	     fputs(convert_char(header[3]<<8|header[2]),stdout); 
	     fputs(convert_char(header[5]<<8|header[4]),stdout); 
	     fputs(convert_char(header[7]<<8|header[6]),stdout); 
        }	    
        while (!feof(f)) {
	   i=get_unicode_char(f,&offset); 
           if (i!=EOF) fputs(convert_char(i),stdout);
	}    
    } else {
	/* Assuming 8-bit input text */
       while ((count = fread(buf,1,PARAGRAPH_BUFFER,f))) {
	   for (i=0;i<count;i++) {
	       fputs(convert_char(to_unicode(source_charset,(unsigned char)buf[i])),stdout);
           }		       
       }
    } 
} 
/**************************************************************************/
/*  process_file - main process engine. Reads word file using function,   */
/*  pointed by get_unicode_char, searches for things which looks like     */
/*  paragraphs and print them out                                         */
/**************************************************************************/
void process_file(FILE *f,long stop) {
    int bufptr;
    int tabmode=0;
    long offset=0;
    unsigned short c;
    /* Now we are starting to read with get_unicode_char */
    while (!feof(f)&&offset<stop) {
        bufptr = -1;
        do {
             c=get_unicode_char(f,&offset);
	    /* Following symbols below 32 are allowed inside paragraph:
               0x0002 - footnote mark
	       0x0007 - table separator (converted to tabmode)
               0x0009 - Horizontal tab ( printed as is)
	       0x000B - hard return
               0x000D - return - marks an end of paragraph
               0x001E - IS2 for some reason means short defis in Word.
	       0x001F - soft hyphen in Word
               0x000B - means same as return
             */
	     if (tabmode) {
		 tabmode=0;
	         if (c==0x007) {
	           buffer[++bufptr]=0x1E;
		   continue;
		 } else {
		   buffer[++bufptr]=0x1C;
		 }  
	     }   	 
             if (c<32) {
                 switch (c) {
                    case 0x007:
                            tabmode = 1;
                            break;
                    case 0x000D:
                    case 0x000B:
                        buffer[++bufptr]=0x000A;
                        break;
                    case 0x001E:
                        buffer[++bufptr]='-';
                        break;
                    case 0x0002: break;
		    case 0x001F:
		        buffer[++bufptr]=0xAD;/* translate to Unicode
			                         soft hyphen */
                        break;						  
                    case 0x0009:
                        buffer[++bufptr]=c;
                        break;
                    default:
                        bufptr=-1; /* Any other control char - discard para*/
                }
	    } else if (c >=0xfeff) {
                bufptr = -1;/* not a valid unicode - discard paragraph */
            } else {
                buffer[++bufptr]=c;
            }
        } while (bufptr<PARAGRAPH_BUFFER-2&&!feof(f) && buffer[bufptr]!=0x000a);
	if (bufptr>0) {
	   buffer[++bufptr]=0;
           output_paragraph(buffer);
        }
    }

}

int get_word8_char(FILE *f,long *offset) {
  int count,i,u;
  char c;
  if ((i=(*offset)%256) ==0) {
     count=fread(read_buf,1,256,f);
     memset(read_buf+count,0,256-count);
     buf_is_unicode=0;
     while (i<count) {
       c=read_buf[i++];
       if ((c==0x20|| c==0x0D||ispunct(c))&&i<count&&read_buf[i]==0) {
	   buf_is_unicode=1;
	   break;
       }
       i++;
     }   
     i=0;
  }    
  if (buf_is_unicode) {
      u=read_buf[i] | read_buf[i+1]<<8;
      (*offset)+=2;
  } else {
      u=to_unicode(source_charset,read_buf[i]);
      (*offset)++;
  }
  return u;
}  
 

