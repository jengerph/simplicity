#!/usr/bin/perl

use strict;

use DBI;

my $dbh = DBI->connect("DBI:mysql:accounting", "root", "");

my $sql = "select username, acctsessionid, acctstarttime, acctupdatetime, framedipaddress, nasipaddress from radacct where connectinfo_stop = 0 and acctterminatecause = '' and servicetype = 'Framed-User'";

if ($ARGV[0] ne '') {

	$sql .= " AND username = " . $dbh->quote($ARGV[0]);
} else {
	print "Username required\n";
	exit(1);
}

$sql .= " order by username";

my $sth = $dbh->prepare( $sql );
if ( !defined $sth ) {
    die "Cannot prepare statement: $DBI::errstr\n";
}

# Execute the statement at the database level
$sth->execute();

while (my ( $username, $acctsessionid, $start, $update, $ip, $nasip ) = $sth->fetchrow()) {

open ("RADIUS" , "|/usr/bin/radclient -x -r 1 $nasip:1700 disconnect rg0943");
print RADIUS "Acct-Session-Id=" . $acctsessionid . "\n";
print RADIUS "User-Name=" . $username . "\n";
print RADIUS "NAS-IP-Address=" . $nasip . "\n";
close("RADIUS");


}

$sth->finish();


