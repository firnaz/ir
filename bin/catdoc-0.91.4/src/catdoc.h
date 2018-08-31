#ifndef CATDOC_H
#define CATDOC_H
/* There is some strange thing on aix */
#if (defined(_AIX)||defined(___AIX)) && !defined(__unix)
# define __unix 1
#endif
#include <stdio.h>
#include <ctype.h>
/*
 * User customization
 *
 */

#if defined(__unix) || defined(__unix__)
#ifndef USERRC
#define USERRC ".catdocrc"
#endif
#ifndef SYSTEMRC
#define SYSTEMRC "/usr/local/lib/catdoc/catdocrc"
#endif
#ifndef CHARSETPATH
#define CHARSETPATH "/usr/local/lib/catdoc"
#endif
/* Macro to add executable directory in place of %s in path.
   Not usable in Unix, where executable can have more then one
   link and configuration files are usially kept separately   from executables
 */
#define add_exe_path(name) name
#define LIST_SEP ':'
#define DIR_SEP '/'
#else
#define USERRC "catdoc.rc"
/* In DOS, %s in path gets replaced with full path to executable including
   trailing backslash.
 */
#define SYSTEMRC "%s\\catdoc.rc"
#define CHARSETPATH "%s\\charsets"
char *add_exe_path(const char* name);
#define LIST_SEP ';'
#define DIR_SEP '\\'
#endif

#define CHARSET_EXT ".txt"
#if defined(__unix) || defined(__unix__)
#ifndef TARGET_CHARSET
#define TARGET_CHARSET "koi8-r"
#endif
#ifndef SPEC_EXT
#define SPEC_EXT ".specchars"
#endif
#ifndef REPL_EXT
#define REPL_EXT ".replchars"
#endif
#define PARAGRAPH_BUFFER 262144
#define FILE_BUFFER 262144
#define PATH_BUF_SIZE 1024
#else
/* if it is not unix, its probably DOS*/
#define TARGET_CHARSET "cp866"
#define SPEC_EXT ".spc"
#define REPL_EXT ".rpl"
#define PARAGRAPH_BUFFER 16384
#define FILE_BUFFER  32256
#define PATH_BUF_SIZE 80 
/* no use for pathnames longer than 80 chars in fat FS */
#endif
#ifndef SOURCE_CHARSET
#define SOURCE_CHARSET "cp1251"
#endif
#ifndef UNKNOWN_CHAR
#define UNKNOWN_CHAR '?'
#endif
/* Buffer for single line. Should be greater than wrap margin +
  longest substitution sequence */
#define LINE_BUF_SIZE 512
/*   Default value for wrap margin */
#ifndef WRAP_MARGIN
#define WRAP_MARGIN 72
#endif
/* variable (defined in catdoc.c) which holds actual value of wrap margin*/
extern  int wrap_margin;
/*
 * Public types variables and procedures which should be avalable
 * to all files in the program
 */

#ifdef __TURBOC__
#undef isspace
#define isspace(c) ((unsigned char)(c) <=32)
#endif

/* Structure to store UNICODE -> target charset mappings */
/* array of 256 pointers (which may be null) to arrays of 256 short ints
   which contain 8-bit character codes or -1 if no matching char */
typedef short int  ** CHARSET;

/* structure to store multicharacter substitution mapping */
/* Array of 256 pointers to arrays of 256 pointers to string */
/* configuration variables defined in catdoc.c */
typedef char *** SUBSTMAP;

extern short int *source_charset;
extern char bad_char[]; /* defines one-symbol string to replace unknown unicode chars */
extern char *source_csname;
extern char *dest_csname;
extern char *format_name;
extern CHARSET target_charset;
extern SUBSTMAP spec_chars;
                /* Defines unicode chars which should be
                replaced by strings before UNICODE->target chatset
                mappigs are applied i.e. TeX special chars like %
                */
extern SUBSTMAP replacements;
                 /* Defines unicode chars which could be
                    mapped to some character sequence if no
                    corresponding character exists in the target charset
                    i.e copyright sign */
extern int verbose; /* if true, some additional information would be
		       printed. defined in analyze.c */
extern int (*get_unicode_char)(FILE *f,long *offset); 
/* pointer to function which gets
                                     a char from stream */

extern int get_utf16lsb (FILE *f,long *offset);
extern int get_utf16msb (FILE *f,long *offset);
extern int get_utf8 (FILE *f,long *offset);
extern int get_8bit_char (FILE *f,long *offset);

extern int get_word8_char (FILE *f,long *offset);
extern  short int *read_charset(char *filename);
extern CHARSET make_reverse_map (short int *charset);

extern int to_unicode (short int *charset, int c) ;

extern int from_unicode (CHARSET charset, int u) ;

extern char* convert_char(int unicode_char);

extern char* map_path, *charset_path;
extern int signature_check;
extern int unknown_as_hex;
char *find_file(char *name, const char *path);
char *stradd(const char *s1, const char *s2);
void read_config_file(const char *filename);
SUBSTMAP read_substmap(char* filename);
extern int longest_sequence;/* for checking which value of wrap_margin
                             can cause buffer overflow*/
char *map_subst(SUBSTMAP map,int uc);

void check_charset(char **filename,const char *charset);
void process_file(FILE *f,long stop);
void copy_out(FILE *f, char *header);
void output_paragraph(unsigned short int *buffer) ;
void parse_rtf(FILE *f);
/* format recognition*/
void analyze_format(FILE *f);
void list_charsets(void);
void parse_word_header(unsigned char *buffer,FILE *f,int offset,long curpos);
/* large buffers for file IO*/
extern char *input_buffer,*output_buffer;
#endif
