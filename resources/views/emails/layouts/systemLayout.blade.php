<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Hello!</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            min-width: 100% !important;
        }

        img {
            height: auto;
        }

        .content {
            width: 100%;
            max-width: 600px;
        }

        .header {
            padding: 40px 30px 20px 30px;
        }

        .innerpadding {
            padding: 30px 30px 30px 30px;
        }

        .borderbottom {
            border-bottom: 1px solid #f2eeed;
        }

        .subhead {
            font-size: 15px;
            color: #ffffff;
            font-family: sans-serif;
            letter-spacing: 10px;
        }

        .h1,
        .h2,
        .bodycopy {
            color: #153643;
            font-family: sans-serif;
        }

        .h1 {
            font-size: 33px;
            line-height: 38px;
            font-weight: bold;
        }

        .h2 {
            padding: 0 0 15px 0;
            font-size: 24px;
            line-height: 28px;
            font-weight: bold;
        }

        .bodycopy {
            font-size: 16px;
            line-height: 22px;
        }

        .button {
            text-align: center;
            font-size: 18px;
            font-family: sans-serif;
            font-weight: bold;
            padding: 0 30px 0 30px;
        }

        .button a {
            color: #ffffff;
            text-decoration: none;
        }

        .footer {
            padding: 20px 30px 15px 30px;
        }

        .footercopy {
            font-family: sans-serif;
            font-size: 14px;
            color: #ffffff;
        }

        .footercopy a {
            color: #ffffff;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <table id="background-table" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
            <tr>
                <td align="center">
                    <table class="w640" border="0" cellpadding="0" cellspacing="0" width="640">
                        <tbody>
                            <tr class="large_only">
                                <td class="w640" height="20" width="640"></td>
                            </tr>
                            <tr class="mobile_only">
                                <td class="w640" height="10" width="640"></td>
                            </tr>
                            <tr class="mobile_only">
                                <td class="w640" height="10" width="640"></td>
                            </tr>
                            <tr class="mobile_only">
                                <td class="w640" align="center" width="640">
                                    <table class="w640" border="0" cellpadding="0" cellspacing="0" width="640">
                                        <tr class="mobile_only">
                                            <td class="w40" width="40"></td>
                                            70000TONS OF METAL
                                            <td class="w40" width="40"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr class="large_only">
                                <td class="w640" height="20" width="640"></td>
                            </tr>
                            <tr>
                                <td class="w640" width="640" colspan="3" height="20"></td>
                            </tr>
                            <tr>
                                <td id="header" class="w640" align="center" width="640">
                                    <table class="w640" border="0" cellpadding="0" cellspacing="0" width="640">
                                        <tr>
                                            <td class="w30" width="30"></td>
                                            <td id="logo">
                                                70000TONS OF METAL
                                            </td>
                                            <td class="w30" width="30"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" height="20" class="large_only"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" height="20" class="large_only"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td class="w640" bgcolor="#ffffff" width="640">
                                    <table class="w640" border="0" cellpadding="0" cellspacing="0" width="640">
                                        <tbody>

                                            @section('title')
                                            @show

                                            @section('content')
                                            @show

                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="w640" bgcolor="#ffffff" width="640" colspan="3" height="20"></td>
                            </tr>
                            <tr>
                                <td class="w640" bgcolor="#ffffff" width="640" colspan="3" height="20">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table width="640" class="w640" align="center" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td class="w50" width="50"></td>
                                            <td valign="top">
                                                <table align="right">
                                                    <tr>
                                                        <td colspan="2" height="10"></td>
                                                    </tr>
                                                    <tr>

                                                    </tr>
                                                </table>
                                            </td>
                                            <td class="w15" width="15"></td>
                                        </tr>

                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="w640" width="640" colspan="3" height="20"></td>
                            </tr>
                            <tr>
                                <td id="footer" class="w640" height="60" width="640" align="center">

                                    @section('footer')
                                    @show

                                </td>
                            </tr>
                            <tr>
                                <td class="w640" width="640" colspan="3" height="40"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
