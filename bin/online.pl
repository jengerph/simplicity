#!/usr/bin/perl

use strict;

use DBI;

my $dbh = DBI->connect("DBI:mysql:accounting", "root", "");

my $sql = "select username, acctsessionid, acctstarttime, acctupdatetime, nasipaddress, framedipaddress from radacct where connectinfo_stop = 0 and acctterminatecause = '' and servicetype = 'Framed-User'";

if ($ARGV[0] ne '') {

	$sql .= " AND username like " . $dbh->quote($ARGV[0] . '%');
}
$sql .= " order by username";

my $sth = $dbh->prepare( $sql );
if ( !defined $sth ) {
    die "Cannot prepare statement: $DBI::errstr\n";
}

# Execute the statement at the database level
$sth->execute();

print "Username                          Start                Updated              IP               NAS IP\n";
print "================================  ===================  ===================  ===============  ===============\n";
#      12345678901234567890123456789012  1234567890123456789  1234567890123456789  123456789012345  123456789012345

my $count = 0;
while (my ( $username, $acctsessionid, $start, $update, $nasip, $ip ) = $sth->fetchrow()) {

	printf("%32s  %19s  %19s  %15s  %15s\n", $username, $start, $update, $ip, $nasip);

	$count++;
}

$sth->finish();

print "\n";
print "Total sessions: $count\n";


