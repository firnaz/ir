#include <unistd.h>
#include <ctype.h>
#include <string.h>
#include "catdoc.h"
char *format_name="ascii";
/********************************************************************/
/*  Reads configuration file                                        */
/*                                                                  */
/********************************************************************/
void read_config_file(const char* filename)
{ 
  FILE *f=fopen(add_exe_path(filename),"rb");
  char *name,*value,line[1024],*c;
  int lineno=0;
  if (!f) return;
  while (!feof(f)) {
    fgets(line,1024,f);
    if (feof(f)) break;
    lineno++;
    if ((c=strchr(line,'#'))) *c='\0';
    name=line;
    while (*name&&isspace(*name)) name++;
    if (!*name) continue;
    for (value=name;*value&&(isalnum(*value)||*value=='_'); value++);  
    if (*value=='=') {
       *value=0;value++;
    } else {
      *value=0;value++;
      while(*value&&isspace(*value)) value++;
      if (*value++ != '=' ) {
        fprintf(stderr,"Error %s(%d): name = value syntax expected\n",
              filename,lineno);
        continue;
      }
      while(*value&&isspace(*value)) value++;
    }
    for (c=value;*c&&!isspace(*c);c++);
    if (value==c) {
        fprintf(stderr,"Error %s(%d): name = value syntax expected\n",
              filename,lineno);
        continue;
    }
    *c=0;
    if (!strcmp(name,"source_charset")) {
       source_csname=strdup(value);
    } else if (!strcmp(name,"target_charset")) {
       dest_csname=strdup(value);
    } else if (!strcmp(name,"format")) {
       format_name=strdup(value);
    } else if (!strcmp(name,"charset_path")) {
       charset_path = strdup(value);
    } else if (!strcmp(name,"map_path")) {
       map_path = strdup(value);
    } else if (!strcmp(name,"unknown_char")) {
       if (*value=='"' && value[1] && value[2]=='"') value++;	
       bad_char[0] = *value;
    } else {
	fprintf(stderr,"Invalid configuration directive in %s(%d):,%s = %s\n",
          filename,lineno,name,value);		
    }	
 }
 fclose(f);
}

