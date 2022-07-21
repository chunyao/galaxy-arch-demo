<?php
$host = '192.168.2.29';

$user = 'mabang';

$password = 'mabang123';

$database = 'mabang_publishlog';

$charset = 'utf8';

$socket = '/tmp/proxysql.sock';

$dsn = "mysql:dbname={$database};charset={$charset}";

//if (empty($_GET['proxysql'])) {
//$dsn .= ";host={$host}";

//} else {
$dsn .= ';unix_socket=/tmp/proxysql.sock';

//}

echo $dsn .'
';

$dbh = new PDO($dsn, $user, $password);

$sql = 'SELECT * FROM DB_Log1 LIMIT 10';

//$value = $dbh->query($sql);
//print_r($value);
echo "Run by CYJ," . date('Y-m-d h:i:s', time())."\n";
for($i=0;$i<1000;$i++){
    $value = $dbh->query($sql);
    $dbh -> fetch();
    print_r($value);
}
echo "Run by CYJ," . date('Y-m-d h:i:s', time())."\n";
?>