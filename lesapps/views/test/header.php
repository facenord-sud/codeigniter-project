<html lang="fr">
    <head>
        <title>Unit test results</title>
        <meta charset="utf-8"/>
        <style type="text/css">
            * { font-family: Arial, sans-serif; font-size: 14pt }
            #results { width: 100% }
            .err, .pas { color: white; font-weight: bold; margin: 2px 0; padding: 5px; vertical-align: top; }
            .err { background-color: red }
            .pas { background-color: green }
            .detail { padding: 8px 0 8px 20px }
            a { 
                color: white;
                text-decoration: none;
            }
            a:hover {
                background-color: yellow;
                color: black;
            }
            h1 a{ 
                font-size: 20pt;
                color: black;
            }

        </style>
    </head>
    <body>

        <h1><a href="<?php echo base_url()."test/Toast_all"; ?>">Toast Unit Tests:</a></h1>

        <ol>
