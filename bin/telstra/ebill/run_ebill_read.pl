#!/usr/bin/perl

use strict;
use String::Util qw(trim);
use IO::Uncompress::Unzip;

# Directory with ctop files in it
my $in_dir = "/home/telstra-b01";

# Processed folder
my $out_dir = "/home/telstra-b01/processed";

opendir(DH, $in_dir);
my @files = readdir(DH);
closedir(DH);

foreach my $file (@files)
{
    # skip . and ..
    next if($file =~ /^\.$/);
    next if($file =~ /^\.\.$/);

    if ($file =~ /^TERSXIN/ || $file =~ /^TEMIXIN/) {

        #print $file . "\n";

        run_file($in_dir, $file, $out_dir);
    }
}

sub run_file() {

        my ($dir, $file, $out_Dir) = @_;
        
        print $file . "\n";
        
        my $fn = $dir . '/' . $file;
        
        my $tmp_dir = "/tmp/ebill-" . time();
        
        mkdir($tmp_dir) or die ("Unable to create tmp folder");
        
				system("unzip $fn -d $tmp_dir");
        
        print $tmp_dir . "\n";

        opendir(DH, $tmp_dir);
        my @files = readdir(DH);
        closedir(DH);
        
        foreach my $file (@files)
        {
            # skip . and ..
            next if($file =~ /^\.$/);
            next if($file =~ /^\.\.$/);
        
            if ($file =~ /^EBILLDAY/) {
              system("/var/www/simplicity/bin/telstra/ebill/read-daily.php $tmp_dir/$file 1");
            } elsif ($file =~ /^EBILLMTH/) {
              system("/var/www/simplicity/bin/telstra/ebill/read-monthly.php $tmp_dir/$file 1");
            }
        }

       	system("rm -fr $tmp_dir");
       	
       	rename($dir . '/' . $file, $out_Dir . '/' . $file);
        #exit;
        

}