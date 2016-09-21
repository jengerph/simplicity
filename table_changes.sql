alter table wholesalers add xero_oauth_token varchar(255) not null;
alter table wholesalers add xero_oauth_token_secret varchar(255) not null;
alter table wholesalers add xero_access_token varchar(255) not null;
alter table wholesalers add xero_access_token_secret varchar(255) not null;
alter table wholesalers add xero_session_handle varchar(255) not null;
alter table wholesalers add xero_name varchar(255) not null;
alter table wholesalers add xero_apikey varchar(255) not null;

alter table customers add xero_contactid varchar(255) not null;

CREATE TABLE telstra_infotrans_files (
file_seq int unsigned not null,
creation date not null,
primary key (file_seq)
);

CREATE TABLE telstra_infotrans_records (
id bigint unsigned not null auto_increment,
seq bigint unsigned,
file_seq int unsigned not null,
type tinyint unsigned not null,
order_id int unsigned not null,
param1 varchar(100) not null,
param2 varchar(100) not null,
param3 varchar(100) not null,
param4 varchar(100) not null,
param5 varchar(100) not null,
param6 varchar(100) not null,
primary key (id),
unique (seq),
ts timestamp,
key (file_seq)
);


