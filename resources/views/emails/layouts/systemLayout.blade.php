<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>@yield('title')</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <style type="text/css">
    body {
      background-color: #000;
      font-family: 'Verdana', sans-serif;
      color: #fefefe;
      line-height: 1.6;
      font-size: 14px;
      margin: 0;
      padding: 0;
      min-width: 100% !important;
    }

    .container {
      max-width: 700px;
      margin: 0 auto;
      padding: 20px;
      /* border: 2px solid #444; */
      border-radius: 5px;
      background-color: #000;
    }

    h1 {
      color: #FF0000;
      text-transform: uppercase;
    }

    h2 {
      color: #FF0000;
      text-transform: uppercase;
    }

    p,
    li {
      margin: 15px 0;
      color: #fefefe;
    }

    ul {
      list-style-type: none;
      padding: 0;
    }

    li::before {
      content: "• ";
    }

    a {
      color: #FF0000;
      text-decoration: none;
    }

    .footer {
      margin-top: 20px;
      font-size: 0.8em;
      color: #777;
      border-top: 1px solid #444;
      padding-top: 10px;
      paddding-bottom: 15px;
      text-align: center;
    }

    .logo img {
      max-width: 100%;
      height: auto;
    }

    .social-icons img {
      height: 38px;
      width: 38px;
      margin: 0 5px;
    }

    .regards p {
      margin-top: 20px;
      color: #fefefe;
    }

    table.table-bordered {
      border-collapse: collapse;
      width: 100%;
    }

    table.table-bordered td,
    table.table-bordered th {
      border: 1px solid #333333;
      padding: 8px;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="logo">
      <img src="https://70000tons.com/_mailinglist/media/Jan2020/70K_LOGO_2021.png" alt="70000TONS OF METAL Logo">
    </div>
    <!-- <h2>@yield('header')</h2> -->
    <div class="content">
      @yield('content')
    </div>
    <div class="regards">
      @yield('regards')
    </div>
    <div class="footer">
      <p>70000TONS, 70000TONS OF METAL and the 70000TONS OF METAL logo are registered trademarks <br> 
        of Properties of Metal Ltd. and are used under license. <br>
        © 2009-2025 UMCruises International Ltd. All Rights Reserved.</p>
      <div class="social-icons">
        <a href="http://www.facebook.com/70000TONS" target="_blank">
          <img src="https://70000tons.com/_mailinglist/media/icon/facebook_icon.png" alt="Facebook">
        </a>
        <a href="http://www.instagram.com/70000TONS" target="_blank">
          <img src="https://70000tons.com/_mailinglist/media/icon/instagram_icon.png" alt="Instagram">
        </a>
        <a href="https://www.threads.net/@70000tons" target="_blank">
          <img src="https://70000tons.com/_mailinglist/media/icon/threads_icon.png" alt="Threads">
        </a>
        <a href="http://www.70000TONS.tv" target="_blank">
          <img src="https://70000tons.com/_mailinglist/media/icon/youtube_icon.png" alt="YouTube">
        </a>
        <a href="https://www.tiktok.com/@70000tons?lang=en" target="_blank">
          <img src="https://70000tons.com/_mailinglist/media/icon/tiktok_icon.png" alt="TikTok">
        </a>
        <a href="http://www.twitter.com/70000tons" target="_blank">
          <img src="https://70000tons.com/_mailinglist/media/icon/x_icon.png" alt="Twitter">
        </a>
        <a href="http://www.70000tons.com/forum" target="_blank">
          <img src="https://70000tons.com/_mailinglist/media/icon/forum_icon.png" alt="Forum">
        </a>
        <a href="https://open.spotify.com/user/31wphhaqhspcwfbk7kn2vcicr3wy?si=7b2cd2cac632470d" target="_blank">
          <img src="https://70000tons.com/_mailinglist/media/icon/spotify_icon.png" alt="Spotify">
        </a>
      </div>
    </div>
  </div>
</body>

</html>