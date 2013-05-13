<?php
        print_R("aaaaaaaaaaaa");
        $f  =   @fopen(date('YmdHis').".txt", "w+");
        @fwrite($f, 'bbbbbbbbbbbbbbb');
        @fclose($f);
?>
