# -* wish *-
# fallback which allows me to run wordview.tcl without doing make
if ![info exist charset_lib] {
  set charset_lib /usr/local/lib/catdoc
}
set font 8x13
# Find options (All this can be tuned from dialog)
set FindMode -exact ;# no -regexp for novices
set FindDir -forwards ;# Why not -backwards
set FindCase -nocase ;# Leave it empty if you want to be case sensitive
foreach i {file edit search  help} {
option add *$i.highlightBackground MidnightBlue 
option add *$i.highlightThickness 0
option add *$i.borderWidth 0
}
option add *m.activeBackground MidnightBlue 80 
option add *m.activeForeground white 80 
option add *m.activeBorderWidth 0 80
menubutton .file -text File -menu .file.m -underline 0
set m [menu .file.m]
$m add command -label Open... -command load_file -accelerator Ctrl-O
$m add command -label "Save As..." -command write_file -accelerator Ctrl-S -state disabled
$m add separator
$m add command -label Quit -command exit -accelerator Alt-F4
menubutton .edit -text Edit -menu .edit.m -underline 0 -state disabled
set m [menu .edit.m -postcommand EditEnable]
$m add command -label Copy -command CopySel -accelerator Ctrl-C
$m add separator
$m add command -label "Select All" -accelerator Ctrl-A -command \
 {.text tag add sel 0.0 end}
menubutton .search -text Find -menu .search.m -underline 1 -state disabled
set m [menu .search.m -postcommand EnableSearch]
$m add command -label "Find..." -command FindDialog -accelerator Ctrl-F
$m add command -label "Find Again" -accelerator F3 -command DoFind
#  
# build charset menu
# 
set in_list {Default unicode}
set out_list {Default}
foreach l [glob [file join $charset_lib *.txt]] {
    set n [file rootname [file tail $l]]
    lappend in_list $n
    lappend out_list $n
}

set in_charset Default
set out_charset Default

eval tk_optionMenu .inchar in_charset $in_list
eval tk_optionMenu .outchar out_charset $out_list
.inchar configure -state disabled
.outchar configure -state disabled
label .inlab -text "Input"
label .outlab -text "Output"

trace var in_charset w reread
trace var out_charset w reread
menubutton .help -text Help -menu .help.m -underline 0
set m [menu .help.m]
$m add command -label "About..." -command AboutDialog
text .text -width 80 -height 25 -font $font -xscrollcommand ".xs set" \
    -yscrollcommand ".ys set" -background white -font $font -wrap word \
    -selectforeground white -selectbackground black -spacing3 2m 
.text tag configure sel -relief flat -borderwidth 0
.text tag configure doc -lmargin1 0.2i -lmargin2 0
scrollbar .ys -orient vert -command ".text yview"
scrollbar .xs -orient horiz -command ".text xview"
bind .text <F3> { if [info exists FindPattern] DoFind}
bind .text <Control-O> load_file
bind .text <Control-o> load_file
bind .text <Control-S> {write_file}
bind .text <Control-s> {write_file}
bind .text <Control-F> FindDialog
bind .text <Control-f> FindDialog
grid .file .edit .search  .inlab .inchar .outlab .outchar x .help -sticky w
grid .text - - -  - - - - - .ys
grid .xs - - -  - - - - - 
grid .text -sticky news
grid .xs -sticky we
grid .ys -sticky ns
grid columnconfigure . 0 -weight 0
grid columnconfigure . 1 -weight 0
grid columnconfigure . 2 -weight 0
grid columnconfigure . 3 -weight 0 
grid columnconfigure . 4 -weight 0
grid columnconfigure . 5 -weight 0
grid columnconfigure . 6 -weight 0
grid columnconfigure . 7 -weight 1 
grid columnconfigure . 8 -weight 0
grid columnconfigure . 9 -weight 0
grid rowconfigure . 0 -weight 0
grid rowconfigure . 1 -weight 1
grid rowconfigure . 2 -weight 0

proc load_file {{name {}}} {
global filename
if ![string length $name] {set name [tk_getOpenFile -filetypes {
{{Msword files} .doc}
{{All files} *}} ]}
if ![string length $name] return
if ![file readable $name] {
  return -code error "Cannot open file $name"
}
set filename $name
.inchar configure -state normal
.outchar configure -state normal
.file.m entryconfigure "Save As..." -state normal
.edit configure -state normal
.search configure -state normal
reread
}

proc make_opt {var flag} {
  upvar #0 $var charset
  switch $charset {
	"Default" {return ""}
	"unicode" {return "-u"}
        default {return "$flag $charset"}
  }
}	
proc reread {args} {
global filename in_charset out_charset

set inopt [make_opt in_charset -s]
set outopt [make_opt out_charset -d]
set f [open "|catdoc -w $inopt $outopt \"$filename\"" r]
.text configure -state normal
.text delete 0.0 end
.text insert 0.0 [read $f] doc
.text mark set insert 1.0
.text configure -state disabled
.text see 1.0
if [catch {close $f} msg] {
 tk_messageBox -icon error -title error -message $msg -type ok
 return
}
}
proc write_file {{name {}}} {
    global filename 
    if ![string length $name] {
       set name [tk_getSaveFile -filetypes {
      {{Text files} .txt}
      {{LaTeX files} .tex}}]
    }
    if ![string length $name] return
    if {[file extension $name]==".tex"} {
       eval exec catdoc -t [make_opt in_charset -s] [make_opt out_charset -d]\
		[list $filename] > [list $name]
    } else {
       eval exec catdoc [make_opt in_charset -s] [make_opt out_charset -d]\
		[list $filename]  > [list $name]
    }
}
# -postcommand for Edit menu
proc EditEnable {} {
if [llength [.text tag ranges sel]] {
  .edit.m entryconfigure Copy -state normal
} else {
  .edit.m entryconfigure Copy -state disabled
}
}
proc CopySel {} {
clipboard clear
clipboard append -- [.text get sel.first sel.last]
}
proc FindDialog {} {
make_transient .find "Find" 
frame .find.top
label .find.top.l -text "Find"
entry .find.top.e -width 30 -textvar FindPattern
bind .find.top.e <Key-Return> ".find.b.find invoke"
pack .find.top.l .find.top.e -side left
FindOptionFrame
frame .find.b
button .find.b.find -text "Search" -command DoFind
button .find.b.close -text "Close" -command "destroy .find"
pack .find.b.find .find.b.close -side left -padx 20
pack .find.top -pady 5 -anchor w -padx 10
pack .find.opt -pady 10
pack .find.b
focus .find.top.e
}
proc EnableSearch {} {
global FindPattern ReplaceString
if ![info exists FindPattern] {
  .search.m entryconfigure "Find Again" -state disabled
} else {
  .search.m entryconfigure "Find Again" -state normal
}
}
proc make_transient {wpath title} {
set x [expr [winfo rootx .]+[winfo width .]/3]
set y [expr [winfo rooty .]+[winfo height .]/3]
catch {destroy $wpath}
toplevel $wpath
wm transient $wpath .
wm positionfrom $wpath program
wm geometry $wpath +$x+$y
wm title  $wpath $title
}
proc FindOptionFrame {} {
frame .find.opt
checkbutton .find.opt.dir -variable FindDir -onvalue -backwards\
   -offvalue -forwards  -text Backward
checkbutton .find.opt.regex -variable FindMode -onvalue\
      -regex -offvalue -exact  -text RegExp
checkbutton .find.opt.case -variable FindCase -onvalue -nocase -offvalue {}\
  -text "Ignore case"
pack .find.opt.dir .find.opt.regex .find.opt.case -side left
}
proc DoFind {{quiet 0}} {
global FindPattern FindMode FindDir FindCase
if ![string length $FindPattern] {return 0}
if {$FindMode=="-backwords"} {  
    set stopindex 0.0
} else {
  set stopindex end
} 
set index [eval ".text search $FindCase $FindMode $FindDir -- \
  [list $FindPattern] insert $stopindex"] 
if ![string length $index] {
  if !$quiet {
   tk_messageBox -type ok -title "Not found" -message "Pattern not found"
  }
 return 0
} else {
.text tag remove sel 0.0 end
if {$FindMode=="-exact"} {
.text tag add sel $index "$index + [string length $FindPattern] chars"
} else {
eval "regexp $FindCase --" [list $FindPattern [.text get "$index linestart"\
   "$index lineend"] match]
.text tag add sel $index "$index + [string length $match] chars"
}
.text mark set insert sel.last 
.text see $index
.text see insert
focus .text
return 1
}
}
proc AboutDialog {} {
make_transient .about "About WordView"
message .about.m -aspect 250 -text "MS-Word viewer for UNIX
Copyright (c) by Victor B. Wagner 1997-98
This program is distributed under
GNU General Public License Version 2 or above
Check http://www.gnu.org/copyleft/gpl.html for copying
and warranty conditions" -justify center
button .about.ok -text Ok -command {destroy .about}
pack .about.m .about.ok
}
if [llength $argv] {
 if {![file exist [lindex $argv 0]]} {
    puts stderr "No such file: [lindex $argv 0]"
    exit 1
 }   
load_file [lindex $argv 0]
}
focus .text
