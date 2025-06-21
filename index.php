<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8" />
  <title>مشغل فيديو محمي</title>
  <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
  <style>
    body {
      margin: 0;
      background: #000;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    video {
      width: 90%;
      max-width: 960px;
      border-radius: 16px;
      box-shadow: 0 0 20px #000;
    }
  </style>
</head>
<body>

<video id="player" controls playsinline></video>

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
<script>
fetch('get-token.php')
  .then(res => res.json())
  .then(data => {
    const video = document.getElementById('player');
    const source = data.url + '&file=master.m3u8';

    if (Hls.isSupported()) {
      const hls = new Hls();
      hls.loadSource(source);
      hls.attachMedia(video);
      hls.on(Hls.Events.MANIFEST_PARSED, () => video.play());
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
      video.src = source;
      video.addEventListener('loadedmetadata', () => video.play());
    }

    new Plyr(video, {
      controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
      ratio: '16:9'
    });
  });
</script>

</body>
</html>
