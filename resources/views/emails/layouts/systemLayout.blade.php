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
      padding-bottom: 15px;
      text-align: center;
    }

    .logo img {
      max-width: 100%;
      height: auto;
    }

    .social-icons img {
      height: 20px;
      width: 20px;
      margin: 0 5px 5px;
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

    .booking-table__title {
      font-weight: bold;
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
      <div class="social-icons">
        <a href="https://70000tons.com/forum/" target="_blank">
          <img src="https://70000tons.com/images/icons/forum_white.png" alt="Forum" width="20" height="20">
        </a>
        <a href="https://www.facebook.com/70000tons" target="_blank">
          <img src="https://70000tons.com/images/icons/facebook_white.png" alt="Facebook" width="20" height="20">
        </a>
        <a href="https://instagram.com/70000tons" target="_blank">
          <img src="https://70000tons.com/images/icons/instagram_white.png" alt="Instagram" width="20" height="20">
        </a>
        <a href="https://www.threads.net/@70000tons" target="_blank">
          <img src="https://70000tons.com/images/icons/threads_white.png" alt="Threads" width="20" height="20">
        </a>
        <a href="http://www.youtube.com/70000tons" target="_blank">
          <img src="https://70000tons.com/images/icons/youtube_white.png" alt="YouTube" width="20" height="20">
        </a>
        <a href="https://www.tiktok.com/@70000tons?lang=en" target="_blank">
          <img src="https://70000tons.com/images/icons/tiktok_white.png" alt="TikTok" width="20" height="20">
        </a>
        <a href="https://bsky.app/profile/70000tons.bsky.social" target="_blank">
          <img src="https://70000tons.com/images/icons/bluesky_white.png" alt="Bluesky" width="20" height="20">
        </a>
        <a href="http://www.twitter.com/70000tons" target="_blank">
          <img src="https://70000tons.com/images/icons/xtwitter_white.png" alt="X" width="20" height="20">
        </a>
        <a href="https://t.me/original70000tons" target="_blank">
          <img src="https://70000tons.com/images/icons/telegram_white.png" alt="Telegram" width="20" height="20">
        </a>
        <a href="https://open.spotify.com/user/31wphhaqhspcwfbk7kn2vcicr3wy?si=7b2cd2cac632470d" target="_blank">
          <img src="https://70000tons.com/images/icons/spotify_white.png" alt="Spotify" width="20" height="20">
        </a>
      </div>

      <a href="https://70000tons.com/" style="color: red; text-decoration: underline; text-align: center">www.70000tons.com</a>

      <p>The information in this internet eMail is confidential, may be legally privileged and is intended solely for the Addressee(s) named above. If you are not the intended recipient, or the employee or agent responsible for delivering it to the intended recipient, then any dissemination or copying of this eMail is prohibited and may be unlawful. If you receive this eMail in error, please immediately notify us by return eMail or by telephone. Thank you.</p>
    </div>
  </div>
</body>

</html>