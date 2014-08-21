<?php
include('config.php');

function cliConnect($host, $user, $password, $db, $port)
{
    $mysqli = new mysqli($host, $user, $password, $db, $port);
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }

    return $mysqli;
}

function cliGetResult($connections, $connection, $db, $query, $error)
{
    $host = $connections[$connection]['host'];
    $user = $connections[$connection]['user'];
    $password = $connections[$connection]['password'];
    $port = $connections[$connection]['port'];

    $mysqli = cliConnect($host, $user, $password, $db, $port);
    $result = mysqli_query($mysqli, $query);
    if (!($result))
    {
        $error = date("h:i:s")." - ERROR - {$query} - ".mysqli_error($mysqli);
    }

    if($error)
    {
        die($error);
    }
    else
    {
        return $result;
    }
}

function check($var)
{
    if(isset($var) && $var != '')
    {
        return true;
    }
    else
    {
        return false;
    }
}

$error = '';
$output = '';

if(check($_POST['action']))
{
    if(check($_POST['connection']))
    {
        if(check($connections[$_POST['connection']]['host']))
        {
            $connection = $_POST['connection'];
            $db = '';

            if($_POST['action'] === 'list_dbs')
            {
                $result = cliGetResult($connections, $connection, $db, 'SHOW DATABASES', $error);

                while($row = mysqli_fetch_assoc($result))
                {
                    $output .= "<a href='#' data-db='{$row['Database']}'>+</a> {$row['Database']}<span id='".$row['Database']."_tables'></span><br />";
                }

                $output = substr($output, 0, -6);
            }

            if($_POST['action'] === 'list_tables')
            {
                $output = '<br />';

                $query = "SELECT `table_name`
                FROM `information_schema`.`tables`
                WHERE `table_schema` = '{$_POST['db']}' ";

                $result = cliGetResult($connections, $connection, $db, $query, $error);

                while($row = mysqli_fetch_assoc($result))
                {
                    $output .= "&nbsp;&nbsp;<a href='#' data-table='{$row['table_name']}' data-schema='{$_POST['db']}'>+</a> {$row['table_name']}<span id='".$row['table_name']."_columns'></span><br />";
                }

                $output = substr($output, 0, -6);
            }

            if($_POST['action'] === 'list_columns')
            {
                $output = '<br />';

                $query = "DESCRIBE `{$_POST['db']}`.`{$_POST['table']}` ";

                $result = cliGetResult($connections, $connection, $db, $query, $error);

                while($row = mysqli_fetch_assoc($result))
                {
                    $output .= "&nbsp;&nbsp;&nbsp;&nbsp;&bull; <strong>{$row['Field']}</strong> ".strtoupper($row['Type'])."<br />";
                }

                $output = substr($output, 0, -6);
            }

            if($_POST['action'] == 'run_query')
            {
                $query = $_POST['query'];

                $result = cliGetResult($connections, $connection, $db, $query, $error);

                $output = "<table class='table table-condensed table-striped table-bordered' style='font-size: 11'>";

                // header row
                $output .= "<thead><tr>";
                $row = mysqli_fetch_assoc($result);
                foreach ($row as $col => $value)
                {
                    $output .= "<th>{$col}</th>";
                }
                $output .= "</tr></thead>";

                // data rows
                $output .= "<tbody>";
                mysqli_data_seek($result, 0);
                while($row = mysqli_fetch_assoc($result))
                {
                    $output .= "<tr>";
                    foreach($row as $col => $value)
                    {
                        $output .= "<td>{$value}</td>";
                    }
                    $output .= "</tr>";
                }

                $output .= "</tbody></table>";
            }
        }
    }
}

echo $output;
?>