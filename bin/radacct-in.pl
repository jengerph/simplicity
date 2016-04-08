#!/usr/bin/perl

use DBI;
use Net::SMTP;
use Time::Local;


my $dbh = DBI->connect("DBI:mysql:accounting", "root", "");

if ($ARGV[0] eq 'yesterday') {
	my $yesterday = spGetYesterdaysDate();
	$ARGV[0] = "/var/log/radius/radacct/172.19.1.68/detail-" . $yesterday;
} elsif ($ARGV[0] eq 'today') {
	my $yesterday = spGetTodaysDate();
	$ARGV[0] = "/var/log/radius/radacct/172.19.1.68/detail-" . $yesterday;
} 
open ("IN", $ARGV[0]);

my $emailsend = 0;
if ($ARGV[1] ne '') {
	$emailsend = $ARGV[1];
}	

my $debug = 0;
if ($ARGV[2] ne '') {
	$debug = $ARGV[2];
}	

my %chunk;

my %usage;
my $date = "";
while (my $line = <IN>) {

	chomp($line);
	if ($line eq "") {

	} elsif ($line =~ /^\t/) {
		# Middle bit
		my ($var, $value) = $line =~ /^\t(.*) = (.*)$/;
		$chunk{$var} = $value;
	} else {

		if (scalar(keys(%chunk)) > 0) {

			# We have data

			foreach my $key (keys(%chunk)) {

				#print $key . ' = ' . $chunk{$key} . "\n";
			}

			if ($chunk{'Acct-Status-Type'} eq 'Interim-Update') {

				# Update partial way through
				if ($usage{$chunk{'User-Name'}}{'int-in-start'} ne '') {

					if ($debug == 1) {
						print "Update session for " . $chunk{'User-Name'} . "\n";
					}
					# Update

					if ($usage{$chunk{'User-Name'}}{'int-in-finish'} > $chunk{'Acct-Input-Octets'}) {
						$usage{$chunk{'User-Name'}}{'in-total'} += $usage{$chunk{'User-Name'}}{'int-in-finish'} - $usage{$chunk{'User-Name'}}{'int-in-start'};
						$usage{$chunk{'User-Name'}}{'int-in-start'} = 0;
						if ($debug == 1) {
							print "--rollover input for " . $chunk{'User-Name'} . "\n";
						}
					}
					if ($usage{$chunk{'User-Name'}}{'int-out-finish'} > $chunk{'Acct-Output-Octets'}) {
						$usage{$chunk{'User-Name'}}{'out-total'} += $usage{$chunk{'User-Name'}}{'int-out-finish'} - $usage{$chunk{'User-Name'}}{'int-out-start'};
						$usage{$chunk{'User-Name'}}{'int-out-start'} = 0;
						if ($debug == 1) {
							print "--rollover output for " . $chunk{'User-Name'} . "\n";
						}
					}
					$usage{$chunk{'User-Name'}}{'int-in-finish'} = $chunk{'Acct-Input-Octets'};
					$usage{$chunk{'User-Name'}}{'int-out-finish'} = $chunk{'Acct-Output-Octets'};
				} else {

					if ($debug == 1) {
						print "Handover session for " . $chunk{'User-Name'} . "\n";
					}
					# Session from previous day
					$usage{$chunk{'User-Name'}}{'int-in-finish'} = $chunk{'Acct-Input-Octets'};
					$usage{$chunk{'User-Name'}}{'int-out-finish'} = $chunk{'Acct-Output-Octets'};
					$usage{$chunk{'User-Name'}}{'int-in-start'} = $chunk{'Acct-Input-Octets'};
					$usage{$chunk{'User-Name'}}{'int-out-start'} = $chunk{'Acct-Output-Octets'};
				}
			} elsif ($chunk{'Acct-Status-Type'} eq 'Stop') {

				# Session close
					if ($debug == 1) {
						print "Stop session for " . $chunk{'User-Name'} . "\n";
					}

				if ($usage{$chunk{'User-Name'}}{'int-in-start'} ne '') {
					if ($debug == 1) {
						print "Finalising session for " . $chunk{'User-Name'} . "\n";
					}
					$usage{$chunk{'User-Name'}}{'int-in-finish'} = $chunk{'Acct-Input-Octets'};
					$usage{$chunk{'User-Name'}}{'int-out-finish'} = $chunk{'Acct-Output-Octets'};

					# We have session open to consider

					$usage{$chunk{'User-Name'}}{'in-total'} += $usage{$chunk{'User-Name'}}{'int-in-finish'} - $usage{$chunk{'User-Name'}}{'int-in-start'};
					$usage{$chunk{'User-Name'}}{'out-total'} += $usage{$chunk{'User-Name'}}{'int-out-finish'} - $usage{$chunk{'User-Name'}}{'int-out-start'};

					if ($debug == 1) {
						print "-- Intrim usage for " . $chunk{'User-Name'} . " " . $usage{$chunk{'User-Name'}}{'in-total'} . " " . $usage{$chunk{'User-Name'}}{'out-total'} . "\n";
					}
					$usage{$chunk{'User-Name'}}{'int-in-finish'} = 0;
					$usage{$chunk{'User-Name'}}{'int-out-finish'} = 0;
					$usage{$chunk{'User-Name'}}{'int-in-start'} = 0;
					$usage{$chunk{'User-Name'}}{'int-out-start'} = 0;
				} else {
					# Session closed before we had any updates, ignore as previous day or no data worth considering for this
				}
			} elsif ($chunk{'Acct-Status-Type'} eq 'Start') {

					if ($debug == 1) {
						print "Start Session for " . $chunk{'User-Name'} . "\n";
					}
					$usage{$chunk{'User-Name'}}{'int-in-finish'} = 0;
					$usage{$chunk{'User-Name'}}{'int-out-finish'} = 0;
					$usage{$chunk{'User-Name'}}{'int-in-start'} = 0;
					$usage{$chunk{'User-Name'}}{'int-out-start'} = 0;

			}

			#print "\n";
			#print "=================================================\n";
			#print "\n";
		} 		
		# Start of new bit

		$date = $line;
		%chunk = {};
		
	}

}
close("IN");


foreach my $username (keys(%usage)) {

	# Finish off adding any partial sessions
	$usage{$username}{'in-total'} += $usage{$username}{'int-in-finish'} - $usage{$username}{'int-in-start'};
	$usage{$username}{'out-total'} += $usage{$username}{'int-out-finish'} - $usage{$username}{'int-out-start'};

	#print $username . ' ' . sprintf("%0.2f", $usage{$username}{'in-total'}/1000000) . " " . sprintf("%0.2f", $usage{$username}{'out-total'}/1000000) . "\n";
}

# Determine date
my %months;
$months{'Jan'} = '01';
$months{'Feb'} = '02';
$months{'Mar'} = '03';
$months{'Apr'} = '04';
$months{'May'} = '05';
$months{'Jun'} = '06';
$months{'Jul'} = '07';
$months{'Aug'} = '08';
$months{'Sep'} = '09';
$months{'Oct'} = '10';
$months{'Nov'} = '11';
$months{'Dec'} = '12';

# Wed Jul  1 23:59:56 2015

my ($day, $month, $dayno, $time, $year) = $date =~ /^([A-z]+) ([A-z]+)\s+(\d+) ([\d\:]+) (\d+)$/;

$date = $year . '-' . $months{$month} . '-' . sprintf("%02d", $dayno);

$sql = "DELETE FROM accounting WHERE date = " . $dbh->quote($date);
my $sth = $dbh->prepare($sql);
$sth->execute();

$email = "From: Mr Auditor <alerts\@xi.com.au>\n";
$email .= "To: XI Alerts <alerts\@xi.com.au>\n";
$email .= "Subject: Radius Accounting update for $date\n";
$email .= "\n";
$email .= sprintf("%-50s  %-10s  %-10s", "Username", "Upload", "Download") . "\n";
$email .= "=================================================== =========== ===========\n";

foreach my $username (sort{$usage{$a}{'out-total'} <=> $usage{$b}{'out-total'}} keys(%usage)) {

	my ($user) = $username =~ /^'(.*)'$/;

	print $user . ' ' . sprintf("%0.2f", $usage{$username}{'in-total'}/1000000) . " " . sprintf("%0.2f", $usage{$username}{'out-total'}/1000000) . "\n";

	$email .= sprintf("%-50s   %10.2f  %10.2f", $user, $usage{$username}{'in-total'}/100000, $usage{$username}{'out-total'}/1000000) . "\n";

	$sql = "INSERT INTO accounting (username, date, input, output) values (" . $dbh->quote($user) . "," . $dbh->quote($date) . "," . $dbh->quote($usage{$username}{'in-total'}) . "," . $dbh->quote($usage{$username}{'out-total'}) . ')';

	my $sth = $dbh->prepare($sql);
	$sth->execute();
}

if ($emailsend eq 1) {
	my $smtp = Net::SMTP->new('smtp.xi.com.au') or die $!;
	$smtp->mail( 'alerts@xi.com.au' );
	$smtp->to( 'alerts@xi.com.au' );
	$smtp->data();
	$smtp->datasend($email);
	$smtp->dataend();
	$smtp->quit(); # all done. message sent.

#	print "Email Sent\n";
}


sub spGetYesterdaysDate {
my ($sec, $min, $hour, $mday, $mon, $year) = localtime();
my $yesterday_midday=timelocal(0,0,12,$mday,$mon,$year) - 24*60*60;
($sec, $min, $hour, $mday, $mon, $year) = localtime($yesterday_midday);
my @abbr = qw( Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec );
my $YesterdaysDate = sprintf "%4d%02d%02d", $year+1900, $mon+1, $mday;
return $YesterdaysDate;
}
sub spGetTodaysDate {
my ($sec, $min, $hour, $mday, $mon, $year) = localtime();
my $yesterday_midday=timelocal(0,0,12,$mday,$mon,$year);
($sec, $min, $hour, $mday, $mon, $year) = localtime($yesterday_midday);
my @abbr = qw( Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec );
my $YesterdaysDate = sprintf "%4d%02d%02d", $year+1900, $mon+1, $mday;
return $YesterdaysDate;
}
