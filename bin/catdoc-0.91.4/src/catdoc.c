#ifdef HAVE_CONFIG_H
#include <config.h>
#endif

#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>
#include <ctype.h>
#include "catdoc.h"

void help(void);


int signature_check = 1;
int wrap_margin = WRAP_MARGIN;
int (*get_unicode_char)(FILE *f,long *offset) =NULL;

char *input_buffer, *output_buffer;
#ifdef __WATCOMC__
/* watcom doesn't provide way to access program args via global variable */
/* so we would hack it ourselves in Borland-compatible way*/
char **_argv;
int _argc;
#endif
/**************************************************************/
/*       Main program                                         */
/*  Processes options, reads charsets  files and substitution */
/*  maps and passes all remaining args to processfile         */
/**************************************************************/
int main(int argc, char **argv) {
FILE *f;
int c,i;
char *tempname;
short int *tmp_charset;
int stdin_processed=0;
#ifdef __WATCOMC__
 _argv=argv;
 _argc=argc;
#endif
read_config_file(SYSTEMRC);
#ifdef USERRC
tempname=find_file(strdup(USERRC),getenv("HOME"));
if (tempname) {
  read_config_file(tempname);
  free(tempname);
}
#endif
while ((c=getopt(argc,argv,"ls:d:f:taubxv8wm:"))!=-1) {
   switch (c) {
   case 's':
	check_charset(&source_csname,optarg);
        break;
   case 'd':
	check_charset(&dest_csname,optarg);
        break;
   case 'f':
        format_name=strdup(optarg);
        break;
   case 't':
        format_name=strdup("tex");
        break;
   case 'a':
        format_name=strdup("ascii");
        break;
   case 'u':
         get_unicode_char = get_word8_char;
         break;
   case '8':
         get_unicode_char = get_8bit_char;
         break;
   case 'v':
         verbose=1;
         break;
   case 'w':
         wrap_margin=0; /* No wrap */
         break;
   case 'm': {
         char *endptr;
         wrap_margin = strtol(optarg,&endptr,0);
         if (*endptr) {
            fprintf(stderr,"Invalid wrap margin value `%s'\n",optarg);
            exit(1);
         }
         break;
      }
   case 'l': list_charsets(); exit(0);	     
   case 'b': signature_check =0; break;
   case 'x': unknown_as_hex = 1; break;
   default:
        help();
        exit(1);
   }
}
input_buffer=malloc(FILE_BUFFER);
if (!input_buffer) {
  fprintf(stderr,"Input buffer not allocated\n");
}
source_charset = read_charset(source_csname);
if (!source_charset) exit(1);
tmp_charset = read_charset(dest_csname);
if (!tmp_charset) exit(1);
target_charset= make_reverse_map(tmp_charset);
free(tmp_charset);

spec_chars=read_substmap(stradd(format_name,SPEC_EXT));
if (!spec_chars) {
   fprintf(stderr,"Cannod read substitution map %s%s\n",format_name,
   SPEC_EXT);
  exit(1);
}  
replacements=read_substmap(stradd(format_name,REPL_EXT));
if (!replacements) {
   fprintf(stderr,"Cannod read substitution map %s%s\n",format_name,
   REPL_EXT);
  exit(1);
}  

if (LINE_BUF_SIZE-longest_sequence<=wrap_margin) {
  fprintf(stderr,"wrap margin is too large. cannot proceed\n");
  exit(1);
}  
if (!isatty(fileno(stdout))) {
   output_buffer=malloc(FILE_BUFFER);
   if (output_buffer) {
     if  (setvbuf(stdout,output_buffer,_IOFBF,FILE_BUFFER)) {
          perror("stdout");
     }
   } else {
     fprintf(stderr,"output buffer not allocated\n");
   }
}
if (optind == argc) {
  if (isatty(fileno(stdin))) {
     help();
     exit(0);
  }
  if (input_buffer) setvbuf(stdin,input_buffer,_IOFBF,FILE_BUFFER);
  analyze_format(stdin);
  return 0;
}
c=0;
for (i=optind;i<argc;i++) {
  if (!strcmp(argv[i],"-")) {
     if (stdin_processed) {
       fprintf(stderr,"Cannot process stdin twice\n");
       exit(1);
     }
     if (input_buffer) setvbuf(stdin,input_buffer,_IOFBF,FILE_BUFFER);
     analyze_format(stdin);
     stdin_processed=1;
  } else {
    f=fopen(argv[i],"rb");
    if (!f) {
       c=1;
       perror("catdoc");
       continue;
    }
  if (input_buffer)
    if (setvbuf(f,input_buffer,_IOFBF,FILE_BUFFER)) {
       perror(argv[i]);
    }
    analyze_format(f);
    fclose(f);
  }
}

  return c;
}
/************************************************************************/
/* Displays  help message                                               */
/************************************************************************/
void help (void) {
  printf("Usage:\n catdoc [-vu8btawxl] [-m number] [-s charset] [-d charset] [ -f format] files\n");
}
