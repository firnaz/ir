#include <stdio.h>
#include <limits.h>
#include <string.h>
#include "catdoc.h"
char ole_sign[]={0xD0,0xCF,0x11,0xE0,0xA1,0xB1,0x1A,0xE1,0};
char rtf_sign[]="{\\rtf";
char write_sign[]={0x31,0xBE,0};
int verbose=0;
int getlong(char *buffer,int i);
int getshort(char *buffer,int i);

void analyze_format(FILE *f) {
    unsigned char buffer[129];
    long offset=0;
   if (!signature_check) {
      /* forced parsing */
      /* no autodetect possible. Assume 8-bit if not overriden on
       * command line */ 
      if (!get_unicode_char) 
	  get_unicode_char=get_8bit_char;
      process_file(f,LONG_MAX);
      return;
   }
   fread(buffer,4,1,f);
   buffer[4]=0;
   if (strncmp(buffer,write_sign,2)==0) {
       printf("[Windows Write file. Some garbage expected]\n");
       process_file(f,LONG_MAX);
       return;
   } else if (strncmp(buffer,rtf_sign,4)==0) {
       parse_rtf(f);
   } else if ((unsigned char)buffer[1]==0xA5 && buffer[0] & 0x80) {
    
     fread(buffer+4,124,1,f);
     buffer[128]=0;
     parse_word_header(buffer,f,0,0);
     return;
   } 
   fread(buffer+4,4,1,f);
   if (strncmp(buffer,ole_sign,8)!=0) {
      /* Unrecognized, seems to be plain text */
      copy_out(f,buffer);
      return;
   }
   fread(buffer+8,120,1,f);
   offset = 128;
   while (fread(buffer,1,128,f)==128) {
     buffer[128]=0;
     if ((unsigned char)buffer[1]==0xA5 && buffer[0] & 0x80) {
	 parse_word_header(buffer,f,-128,offset);
       return;
     }
     offset+=128;
   } 
   printf("No word signature found. Probably it is another OLE application\n");
   return;
}   
#define fDot 0x0001   
#define fGlsy 0x0002
#define fComplex 0x0004
#define fPictures 0x0008 
#define fEncrypted 0x100
#define fReadOnly 0x400
#define fReserved 0x800
#define fExtChar 0x1000

void parse_word_header(unsigned char * buffer,FILE *f,int offset,long curpos) {
   int flags,charset;
   long textstart,textlen,i;
   if (verbose) {
   printf("File Info block version %d\n",getshort(buffer,2));
   printf("Found at file offset %ld (hex %lx)\n",curpos,curpos);
   printf("Written by product version %d\n",getshort(buffer,4));
   printf("Language %d\n",getshort(buffer,6));
   }
   flags = getshort(buffer,10);
   if (verbose) {
   if ((flags & fDot)) {
     printf("This is template (DOT) file\n");
   } else {
     printf("This is document (DOC) file\n");
   }
   if (flags & fGlsy) {
     printf("This is glossary file\n");
   }
   }
   if (flags & fComplex) {
    printf("[This was fast-saved %2d times. Some information is lost]\n",
     (flags & 0xF0)>>4);
   }
   if (flags & fEncrypted) {
     printf("[File is encrypted. Encryption key = %08x\n]",
     getlong(buffer,14));
     return;
   }
   if (verbose) {
     if (flags & fReadOnly) {
        printf("File is meant to be read-only\n");
     }
     if (flags & fReserved) {
       printf("File is write-reserved\n");
    }
   }
   if (flags & fExtChar) {
      if (verbose) {
       printf ("File uses extended character set\n");
      }
      if (!get_unicode_char) 
	  get_unicode_char=get_word8_char;
      
   } else if (!get_unicode_char) 
        get_unicode_char=get_8bit_char;
   
   if (verbose) {
   if (buffer[18]) {
     printf("File created on Macintosh\n");
   } else {
     printf("File created on Windows\n");
   } 
   }
   if (verbose) {
   charset=getshort(buffer,20);
   if (charset&&charset !=256) {
     printf("Using character set %d\n",charset);
   } else {
     printf("Using default character set\n");
   }
   }
   /* skipping to textstart and computing textend */
   textstart=getlong(buffer,24);
   textlen=getlong(buffer,28)-textstart;
   textstart+=offset;
   if (verbose) {
      printf ("Textstart = %ld (hex %lx)\n",textstart+curpos,textstart+curpos);
      printf ("Textlen =   %ld (hex %lx)\n",textlen,textlen);
   }   
   for (i=0;i<textstart;i++) {
     fgetc(f);
     if (feof(f)) {
	fprintf(stderr,"File ended before textstart. Probably it is broken. Try -b switch\n");
	exit(1);
     }
   }    
   process_file(f,textlen);
}   

int getshort(char *buffer,int i) {
  return (unsigned char)buffer[i]|((unsigned char)buffer[i+1]<<8);
}  
int getlong(char *buffer,int i) {
  return (unsigned char)buffer[i]|((unsigned char)buffer[i+1]<<8)
   |((unsigned char)buffer[i+2]<<16)|((unsigned char)buffer[i+3]<<24);
}  
