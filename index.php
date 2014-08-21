<html>

<head>

    <title>Web SQL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- jQuery -->
    <script src="js/jquery-1.11.1.min.js" type="text/javascript"></script>

    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">

    <!-- Codemirror -->
    <link href="css/codemirror.css" rel="stylesheet">
    <script src="js/codemirror.js"></script>
    <script src="js/sql.js"></script>
    <script src="js/active-line.js"></script>
</head>

<body>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span3">
            <div>
                <select id="connection">
                    <option name='connection' value=''></option>
                    <?php
                        include('config.php');

                        foreach($connections as $key => $value)
                        {
                            echo "<option name='connection' value='{$key}'>{$key}</option>";
                        }
                    ?>
                </select>
            </div>
            <div id="tables_list" style="height: 90%; overflow: auto;">
                <span>Select a host from the dropdown above.</span>
            </div>
        </div>
        <div class="span9">
            <div id="query_area">
                <textarea id="code" name="code"></textarea>
            </div>

            <div id="results_area" style="height: 55%; overflow: auto;">
            </div>
        </div>
    </div>
</div>


<script>
    var editor = CodeMirror.fromTextArea(document.getElementById("code"),
    {
        styleActiveLine: true,
        lineWrapping: true,
        mode: 'text/x-mysql',
        indentWithTabs: true,
        smartIndent: true,
        lineNumbers: true,
        matchBrackets : true,
        autofocus: true,
        extraKeys:
        {
            "F9": function(event)
            {
                var selected = editor.getSelection(' ').trim();

                // run the query
                $('#results_area').html('<img src="ajax-loader.gif">');

                $.post(
                    'ajax.php',
                    {
                        'connection': $('#connection').val(),
                        'action': 'run_query',
                        'query': selected
                    },
                    function($data)
                    {
                        $('#results_area').html($data);
                    }
                );
            }
        }
    });

    $(function()
    {
        // load some initial data
        $('#connection').on('change', function()
        {
            $('#tables_list').html('<img src="ajax-loader.gif">');

            $.post(
                'ajax.php',
                {
                    'connection': $('#connection').val(),
                    'action': 'list_dbs'
                },
                function($data)
                {
                    $('#tables_list').html($data);
                }
            );
        });

        // show tables when clicking +
        $('#tables_list').on('click', 'a[data-db]', function()
        {
            var tmp = '#' + $(this).data('db') + '_tables';

            if($(this).text() === '+')
            {
                $(this).text('-');

                $.post(
                    'ajax.php',
                    {
                        'connection': $('#connection').val(),
                        'action': 'list_tables',
                        'db': $(this).data('db')
                    },
                    function($data)
                    {
                        $(tmp).html($data);
                    }
                );
            }
            else
            {
                $(this).text('+');
                $(tmp).html('');
            }
        });

        // show columns
        $('#tables_list').on('click', 'a[data-table]', function()
        {
            var tmp = '#' + $(this).data('table') + '_columns';

            var table = $(this).data('table');
            var db = $(this).data('schema');

            if($(this).text() === '+')
            {
                $(this).text('-');

                $.post(
                    'ajax.php',
                    {
                        'connection': $('#connection').val(),
                        'action': 'list_columns',
                        'db': db,
                        'table': table
                    },
                    function($data)
                    {
                        $(tmp).html($data);
                    }
                );
            }
            else
            {
                $(this).text('+');
                $(tmp).html('');
            }
        });
    });
</script>

</body>
</html>