<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>@yield('title')</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            min-width: 100% !important;
        }

        .content {
            width: 100%;
            max-width: 600px;
        }

        .header {
            text-align: center;
            padding: 40px 30px 20px 30px;
            background-color: #000000;
            color: #ffffff;
            font: 18px Arial, sans-serif;
            font-weight: bold;
        }

        .innerpadding {
            padding: 30px;
        }

        .bodycopy {
            font-size: 16px;
            line-height: 22px;
            color: #000000;
        }

        .footer {
            background-color: #000000;
            padding: 20px;
            text-align: center;
            color: #ffffff;
            font: 12px Arial, sans-serif;
            font-weight: bold;
        }

        a {
            color: #1a6aff;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <table class="content" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="header">
                            @yield('header')
                        </td>
                    </tr>
                    <tr>
                        <td class="innerpadding">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            @yield('footer')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
