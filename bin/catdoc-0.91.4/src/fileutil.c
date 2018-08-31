#include <unistd.h>
#include <stdio.h>
#include <string.h>
#include <sys/stat.h>
#include <stdlib.h>
#include "catdoc.h"
#ifdef __TURBOC__
#include <dir.h>
#include <dos.h>
#endif
#if defined(MSDOS) && !defined(__MSDOS__)
#define __MSDOS__
#endif
#ifndef __MSDOS__
#include <glob.h>
#endif
/************************************************************************/
/* Searches for file name in specified list of directories. Sets        */
/* Returns dynamically allocated full path or NULL. if nothing          */
/* appropriate   Expects name to be dynamically allocated and frees it  */
/************************************************************************/
char *find_file(char *name, const char *path)
{ const char *p;
  char *q;
  char path_buf[PATH_BUF_SIZE];
  char dir_sep[2]={DIR_SEP,0};
    for (p=path;p;p=q+1) {
      q=strchr(p,LIST_SEP);
      
      if (q) {
         if (q-p>=PATH_BUF_SIZE) {
	  /* Oops, dir name too long, perhabs broken config file */
	  continue;
	 }  
	 strncpy(path_buf,p,q-p);
	 path_buf[q-p]=0;
      } else {
	q--;
	if (strlen(p)>=PATH_BUF_SIZE) continue;
	strcpy(path_buf,p);
      }
      /* Empty list element means current directory */
      if (!*path_buf) {
	path_buf[0]='.';
	path_buf[1]=0;
#ifdef __MSDOS__
      } else {
	strcpy(path_buf,add_exe_path(path_buf)); /* safe, becouse
	                add_exe_path knows about PATH_BUF_SIZE */
#endif
      }
      strcat(path_buf,dir_sep); /* always one char */
      if (strlen(path_buf)+strlen(name)>=PATH_BUF_SIZE) 
         continue; /* Ignore too deeply nested directories */
      strcat(path_buf,name);
      if (access(path_buf,0)==0) {
         free(name); 
	 return strdup(path_buf);
      }
    }
    /* if we are here, nothing found */
    free(name); 
    return NULL;
}

/************************************************************************/
/* Searches for charset with given name and put pointer to malloced copy*/
/* of its name into first arg if found. Otherwise leaves first arg      */
/*  unchanged                                                           */
/************************************************************************/
void check_charset(char **filename,const char *charset) {
   char *tmppath=find_file(stradd(charset,CHARSET_EXT),charset_path);
   if (tmppath&& *tmppath) {
     *filename=strdup(charset);
     free(tmppath);
   }
}

/**********************************************************************/
/*  Returns malloced string containing concatenation of two           */
/*  arguments                                                         */
/**********************************************************************/
char *stradd(const char *s1,const char *s2) 
{ char *res;
  res=malloc(strlen(s1)+strlen(s2)+1);
  if (!res) {
     fprintf (stderr,"Out of memory!");
     exit(1);
  }
  strcpy(res,s1);
  strcat(res,s2);
  return res;
}  
  

/*
 * In DOS, argv[0] contain full path to the program, and it is a custom
 * to keep configuration files in same directory as program itself
 */
#ifdef __MSDOS__
char *exe_dir(void) {
  static char pathbuf[PATH_BUF_SIZE];
  char *q;
  strcpy(pathbuf,_argv[0]); /* DOS ensures, that our exe path is no
                               longer than PATH_BUF_SIZE*/
  q=strrchr(pathbuf,DIR_SEP);
  if (q) {
    *q=0;
  } else {
    pathbuf[0]=0;
  }
  return pathbuf;
}
char *add_exe_path(const char *name) {
static char path[PATH_BUF_SIZE];
       char *mypath=exe_dir();
  /* No snprintf in Turbo C 2.0 library, so just check by hand
     and exit if something goes wrong */
  if (strchr(name,'%')) {
    /* there is substitution */
    if (strlen(name)-1+strlen(mypath)>=PATH_BUF_SIZE) {
       fprintf(stderr,"Invalid config file. file name \"%s\" too long "
                      "after substitution\n",name);
       exit(1);
    }   
    sprintf(path,name,exe_dir());
    return path;
  } else {
    return name;
  }  
}
#endif 
void list_charsets(void) {
 const char *p;
  char *q;
  char path_buf[PATH_BUF_SIZE];
  char dir_sep[2]={DIR_SEP,0};
#ifdef __MSDOS__
  struct ffblk ffblock;
#else  
  glob_t glob_buf;
  int count,glob_flags=GLOB_ERR;
#endif
  char **ptr;
    for (p=charset_path;p;p=q+1) {
      q=strchr(p,LIST_SEP);
      
      if (q) {
         if (q-p>=PATH_BUF_SIZE) {
	  /* Oops, dir name too long, perhabs broken config file */
	  continue;
	 }  
	 strncpy(path_buf,p,q-p);
	 path_buf[q-p]=0;
      } else {
	q--;
	if (strlen(p)>=PATH_BUF_SIZE) continue;
	strcpy(path_buf,p);
      }
      /* Empty list element means current directory */
      if (!*path_buf) {
	path_buf[0]='.';
	path_buf[1]=0;
#ifdef __MSDOS__
      } else {
	strcpy(path_buf,add_exe_path(path_buf)); /* safe, becouse
	                add_exe_path knows about PATH_BUF_SIZE */
#endif
      }
      strcat(path_buf,dir_sep); /* always one char */
      if (strlen(path_buf)+6>=PATH_BUF_SIZE) 
         continue; /* Ignore too deeply nested directories */
      strcat(path_buf,"*.txt");
#ifdef __MSDOS__
      findfirst(path_buf,&ffblock,FA_RDONLY | FA_HIDDEN | FA_ARCH);
      while (!errno) {
         char name[12],*src,*dest;
	 dest=name;
	 src=ffblock.ff_name;
	 for (dest=name,src=ffblock.ff_name;*src && *src !='.';dest++,src++)
	     *dest=tolower(*src);
	 printf("%10s",name);
	 findnext(&ffblock);
      } 	  
#else        
      switch (glob(path_buf,glob_flags,NULL,&glob_buf)) {
	  case 0:
#ifdef GLOB_NOMATCH	      
	  case GLOB_NOMATCH: 
#endif	      
	      break;
          default:
	      perror("catdoc");
	      exit(1);
      }
      glob_flags|=GLOB_APPEND;
#endif      
   }
#ifdef __MSDOS__
    fputc('\n',stdout)
#else 	
   count=0;printf("Available charsets:"); 
   for (ptr=glob_buf.gl_pathv;*ptr;ptr++) {
       printf("%c",(count++)%5?'\t':'\n');
       p=strrchr(*ptr,dir_sep[0]);
       if (!p) continue;
       p++;
       if ((q=strchr(p,'.'))) *q=0;
       fputs(p,stdout);
   }  
   printf("\n");
   globfree(&glob_buf);
#endif   
}    
