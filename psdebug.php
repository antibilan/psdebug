#!/usr/bin/php

<?php

# written by Danil Dmitrienko

        class Entry {

                public $position;
                public $type;
                public $value;
                public $active;
                public $raw;

                public function __construct($raw, $position=NULL) {
                        $this->raw = $raw;
                        $this->position = $position;

                        if (substr($raw, 0, 1) === ";") {
                                $this->type = "comment";
                        }
                        elseif (trim(preg_match("/^\[.*\]$/",$raw))) {
                                $this->type = "section";
                        }
                        elseif (substr($raw, 0, 1) == NULL || $raw == '') {
                                $this->type = "empty";
                        }
                        else $this->type = "value";

                        if (preg_match("/^;?[^\ ]*\ *={1}\ *([oO]n|[0-9])\ *$/",$this->raw)) { // strict debug =On or debug=on or debug    =     7 and etc
                                $this->active = "true";}
                                else {$this->active = "false";}
                }

                public function uncomment() {
                        $this->raw=substr($this->raw,1);
                }

                public function comment() {
                        $this->raw=";".$this->raw;
                }
        }

        function status(){
                global $ini_file;
                foreach ($ini_file as $key => $value) {
                        if ($value->type == "section" || $value->type == "value") {
                                echo $value->raw ."\n";
                        }
                }
        }

        //возвращает массив из строк секции включая заголовок
        function get_section($name) {

                $sec_count = 0;
                global $ini_file;
                $array = $ini_file;
                $section=array();

                foreach ($array as $key => $value) {

                        if ($value->type == "section" && $sec_count > 0) {

                                #end = $key - 1;
                                $sec_count++; // just in case =) not used for now
                                break;
                                #$key = позиция следующей секции, может быть потом полезна.
                        }
                        if ($value->type == "section" && strpos($value->raw, $name) != false) {

                                #$start = $key;
                                $sec_count++;
                                $section[] = $value;
                                #echo $this->start . "\n";
                        }
                        if (($value->type == "value" || $value->type == "comment" || $value->type == "empty") && $sec_count > 0) {

                                $section[] = $value;
                                $sec_count++;
                        }
                }

                return $section;
        }

        function uncomment_sec_line ($section_name, $line) {

                global $ini_file;
                $section_arr = get_section($section_name);

                foreach ($section_arr as $key => $value) {
                        if ($value->type == "comment" && strpos($value->raw,$line) != false) {
                                $value->uncomment();
                        }
                        $ini_file[$value->position] = $value;
                }
        }

        function enable($mod) {
                if ($mod == "std") {
                        uncomment_sec_line ("debug", "enabled = on");
                        uncomment_sec_line ("log", "filter.priority = 7");
                        uncomment_sec_line ("log", "show.util_exec = on");
                        uncomment_sec_line ("log", "show.util_exec_io = on");
                }
                elseif ($mod == "sql") {
                        uncomment_sec_line ("debug", "enabled = on");
                        uncomment_sec_line ("log", "show.sql_query = on");
                }
                elseif ($mod=="full") {
                        uncomment_sec_line ("debug", "enabled = on");
                        uncomment_sec_line ("log", "show.sql_query = on");
                        uncomment_sec_line ("log", "filter.priority = 7");
                        uncomment_sec_line ("log", "show.util_exec = on");
                        uncomment_sec_line ("log", "show.util_exec_io = on");
                }
        }

        function disable($mod) {

                global $ini_file;

                if ($mod == "debug") {
                        $section_arr1=get_section("debug");
                        $section_arr2=get_section("log");

                        foreach ($section_arr1 as $key => $value) {
                                if ($value->type == "value") {
                                        $value->comment();
                                }
                        }

                        foreach ($section_arr2 as $key => $value) {
                                if ($value->type == "value") {
                                        $value->comment();
                                }
                        }
                }
                elseif ($mod == "full") {
                        foreach ($ini_file as $key => $value) {
                                if ($value->type == "value")$value->comment();
                        }
                }
        }

        function write_file($file) {

                global $ini_file;

                $catRaw = array_map(function ($o) {return $o->raw;}, $ini_file);        //take raw of every Entry in $ini_file
                $line_separated = implode("\n", $catRaw);
                file_put_contents($file, $line_separated);

        }

        function get_help(){

                echo "
                Usage: psdebug [OPTIONS]
        -e,   --enable
                         enable debug without SQL

        -S,   --sql
                         enable only SQL debug

        -f,   --full
                         enable full debug with SQL

        -d,   --disable
                         disable debug

        -d,   --disable-all
                         disable all options in panel.ini file

        -s,   --status
                         display current panel.ini status

        -h,   --help
                         display this help page\n\n";
        }

        $file = "/usr/local/psa/admin/conf/panel.ini";


        $longopts = array("help","enable","disable","disable-all","full","status","sql",);
        $options = getopt("hedfsS",$longopts);


        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
           #echo 'This is a server using Windows!\n';
        } else {
           # echo 'This is a server not using Windows!\n';
        }

        if (!file_exists($file)) {
                $i = 1;
                echo "Panel.ini does not exist. Do you want to create it from panel.ini.sample?\n";
                while ($i == 1) {
                        $answer = trim(fgets(STDIN));
                        if (preg_match('/[Yy]/',$answer)||preg_match('/[Yy]es/',$answer)){
                                exec ('cp /usr/local/psa/admin/conf/panel.ini.sample /usr/local/psa/admin/conf/panel.ini 2>&1');
                                echo "panel.ini has been created!\n";
                                $i++;
                        }
                        elseif(preg_match('/[Nn]/',$answer)||preg_match('/[Nn]o/',$answer)){
                                $i++;
                                #break;
                                exit();
                        } else echo "Type [Y]es or [N]o:\n";
                }
        }

        $file_array = file($file, FILE_IGNORE_NEW_LINES);

        $ini_file = array();

        foreach ($file_array as $key => $value) {
                $ini_file[] = new Entry($value, $key);
        }

        foreach ($options as $option => $value) {
                switch ($option) {
                        case 'e' :
                        case 'enable' :
                                enable("std");
                                write_file($file);
                                echo "Standard Plesk debug has been enabled\n";
                                break ;
                        case 'S' :
                        case 'sql' :
                                enable("sql");
                                write_file($file);
                                echo "Plesk SQL debug has been enabled\n";
                                break ;
                        case 'f' :
                        case 'full' :
                                enable("full");
                                write_file($file);
                                echo "Standard Plesk debug with SQL has been enabled\n";
                                break ;
                        case 'd' :
                        case 'disable' :
                                disable("debug");
                                write_file($file);
                                echo "Plesk debug has been disabled\n";
                                break ;
                        case 'disable-all' :
                                disable("full");
                                write_file($file);
                                echo "All options in the panel.ini file have been commented out\n";
                                break ;
                                case 's' :
                                case 'status' :
                                        status();
                                        break ;
                                case 'h' :
                                case 'help':
                                        get_help();
                                        break ;
                                default:
                        echo "This command requires options!\n";
                 }
         }

?>