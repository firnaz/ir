#include <stdio.h>
#include <ctype.h>
#include <string.h>
#include "catdoc.h"
/************************************************************************/
/* performs paragraph formatting if wrap_margin is non-zero             */
/* gets character sequence and appends it to buffer. If buffer is long  */
/* enough, prints its beginning out                                     */
/************************************************************************/
static char buffer[LINE_BUF_SIZE]="";
void out_char(const char *chunk) {
    if (!wrap_margin) {
       fputs(chunk,stdout);
       return;
    }
    strcat(buffer,chunk); /* This strcat is safe. wrap margin setting
                             code in main.c ensures that wrap_margin is 
			     less than LINE_BUF_SIZE-strlen(largest chunk)
			   */  
    if (strchr(chunk,'\n')) {
      /* End of paragraph */
      char *q = map_subst(spec_chars,'\n');
      fputs(buffer,stdout);
      *buffer=0;
      if (q) fputs(q,stdout);
    } else if (strlen(buffer)>wrap_margin) {
        char *q=buffer,*p=buffer+wrap_margin;
        while (p>buffer&&!isspace(*p)) p--;
        if (p==buffer) {
        /*worst case - nowhere to wrap. Will use brute force */
            fwrite(buffer,wrap_margin,1,stdout);
            fputc('\n',stdout);
            p=buffer+wrap_margin;
        } else {
            *p=0;p++;
	    fputs(buffer,stdout);
	    fputc('\n',stdout);
        }
	for(q=buffer;*p;p++,q++) *q=*p;
	*q=0;
     }
}
void output_paragraph(unsigned short int *buffer) {
    unsigned short int *p;
    for (p=buffer;*p;p++) {
        out_char(convert_char(*p));
    }
    out_char("\n");
}
