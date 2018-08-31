# Generated automatically from Makefile.in by configure.
# Your C compilier and flags
SHELL = /bin/sh

srcdir = .
top_srcdir = ..
prefix = /home/w181070/public_html/ir/bin/catdoc
exec_prefix = ${prefix}
installroot = 

bindir = ${exec_prefix}/bin
libdir = ${exec_prefix}/lib
mandir = ${prefix}/man
INSTALL = /usr/bin/install -c
WISH = 
CC = gcc
build_targets = catdoc xls2csv wordview
install_targets = install-catdoc install-wordview
# Flags to pass to your C compilier
# remove -O2 on HP/UX 9. It is known to cause problems
FLAGS=-Wall -O2 -g 

#Compile-time configuration of catdoc itself. Edit according to taste

#Directory, where all system-wide stuff resides
LIB_DIR=$(libdir)/catdoc
#directory, where catdoc binary would be placed
#directory where to put man page

#System-wide configuration file
SYSTEMRC=$(LIB_DIR)/catdocrc

#Per-user configration file (in user's home directory
USERRC=.catdocrc

# path where to search for charsets. may be colon-separated list of dirs
CHARSETPATH=$(LIB_DIR)

# Extension for map of special character
SPEC_EXT=.specchars

# Extension for map of substitutes (chars which are missing from target
# charset)
REPL_EXT=.replchars

# Target charset - one used on your system console or xterm
TARGET_CHARSET=koi8-r

# Source charset - one which your Word files are usially in unless they
# are UNICODE
SOURCE_CHARSET=cp1251

# Character which would be printed instead of ones, not found in charset
# or replacement map
UNKNOWN_CHAR=?

# END OF COMPILE-TIME CONFIGURATION

DEFINES=-DSYSTEMRC=\"$(SYSTEMRC)\" -DUSERRC=\"$(USERRC)\"\
 -DCHARSETPATH=\"$(CHARSETPATH)\" -DSPEC_EXT=\"$(SPEC_EXT)\"\
 -DREPL_EXT=\"$(REPL_EXT)\" -DUNKNOWN_CHAR=\'$(UNKNOWN_CHAR)\'\
 -DSOURCE_CHARSET=\"$(SOURCE_CHARSET)\" -DTARGET_CHARSET=\"$(TARGET_CHARSET)\"

CFLAGS=$(FLAGS) $(DEFINES)  -DSTDC_HEADERS=1 -DHAVE_UNISTD_H=1 -DHAVE_STRDUP=1 -DHAVE_STRTOL=1 
OBJ=catdoc.o charsets.o substmap.o reader.o writer.o fileutil.o\
	confutil.o analyze.o rtfreader.o

OBJXLS=xls2csv.o charsets.o substmap.o fileutil.o confutil.o

#.c.o:
#	$(CC) -c $(CFLAGS) $*
all: catdoc xls2csv wordview
catdoc: $(OBJ)
	$(CC)  -o catdoc  $(OBJ)
xls2csv: $(OBJXLS)
	$(CC) -o xls2csv $(OBJXLS) -lm
install: install-catdoc install-wordview
install-catdoc:catdoc xls2csv
	../mkinstalldirs $(installroot)$(bindir)
	$(INSTALL) -m 755 catdoc $(installroot)$(bindir)/catdoc
	$(INSTALL) -m 755 xls2csv $(installroot)$(bindir)/xls2csv
	$(INSTALL) -m 755 msxlsview.sh $(installroot)$(bindir)/xlsview
install-wordview: wordview 
	../mkinstalldirs $(installroot)$(bindir)
	$(INSTALL) -m 755 wordview $(installroot)$(bindir)/wordview
wordview: wordview.tcl
	echo "#! $(WISH)" >wordview
	echo set charset_lib "\"$(LIB_DIR)\"">>wordview
	cat wordview.tcl >>wordview
	chmod 0755 wordview
clean:
	rm -f *.o catdoc wordview xls2csv
distclean: clean
	rm Makefile
