<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{$title}}</title>
        <script src="//imgcache.qq.com/open/qcloud/video/vcplayer/TcPlayer-2.3.3.js" charset="utf-8"></script>
        <link href="{{ asset('css/app.css')  }}" rel="stylesheet">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body>
        <div id="live"></div>
        <script src="{{ asset('js/app.js') }}"></script>
    </body>
    <script>
      pull_domain = '{{$pull_domain}}';
      stream_name = '{{$stream_name}}';
      // 拼接拉流地址
      m3u8 = 'http://' + pull_domain+"/live/"+stream_name+".m3u8";
      flv = 'http://' + pull_domain+"/live/"+stream_name+".flv";
      rtmp = 'rtmp://' + pull_domain +"/live/"+ stream_name;
      // 获取页面宽高
      var viewportWidth = window.innerWidth;
      var viewportHeight = window.innerHeight;

      var player = new TcPlayer("live", {
        m3u8: m3u8,
        flv: flv,
        rtmp: rtmp,
        autoplay: true,
        poster: "http://haxibiao.com/logo/haxibiao.com.small.png",
        width: viewportWidth,
        height: viewportHeight,
        live:true,
        wording:{
          2032:"播放链接失效了~ 也许是主播下播了哦~,（错误码:2032）",
          2048:"无法播放视频直播,请检查网络或切换浏览器~（错误码:2048）",
          2:"当前浏览器不支持播放视频直播,请切换浏览器再尝试~（错误码:2）",
          1:"当前网络失联~ 请切换浏览器再尝试~（错误码:1）",
          3:"当前浏览器网络失联~ 请切换浏览器再尝试~（错误码:3）",
        }
    });
  </script>

</html>
