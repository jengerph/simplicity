#!/usr/bin/perl

use strict;

use DBI;

my $dbh = DBI->connect("DBI:mysql:radius", "root", "");

my $sql = "select username, acctsessionid, acctstarttime, acctstoptime, acctinputoctets, acctoutputoctets, framedipaddress from radacct WHERE servicetype = 'Framed-User' AND acctstoptime >= NOW() - interval 7 day";

if ($ARGV[0] ne '') {

	$sql .= " AND username = " . $dbh->quote($ARGV[0]);
} else {
	print "Username required\n";
	exit();
}

$sql .= " order by username";

my $sth = $dbh->prepare( $sql );
if ( !defined $sth ) {
    die "Cannot prepare statement: $DBI::errstr\n";
}

# Execute the statement at the database level
$sth->execute();

print "Username                          Start                Finish               IP             Upload        Download\n";
print "================================  ===================  ===================  =============  ============  ============\n";
#      12345678901234567890123456789012  1234567890123456789  1234567890123456789  1234567890123  123456789012  123456789012

while (my ( $username, $acctsessionid, $start, $stop, $input, $output, $ip ) = $sth->fetchrow()) {

	printf("%32s  %19s  %19s  %13s  %12s  %12s\n", $username, $start, $stop, $ip, $input, $output);
}

$sth->finish();


