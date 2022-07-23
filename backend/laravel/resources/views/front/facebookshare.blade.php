<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:fb="http://ogp.me/ns/fb#" lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $article->title }}</title>
  <meta property="og:site_name"          content="{{ config('app.name') }}">
  {{-- <meta property="og:url"                content="{{ route('api.facebookshare', ['article_id' => $article->id, 'user_id' => $user->id]).'?as=1' }}" /> --}}
  <meta property="og:type"               content="website" />
  <meta property="og:title"              content="{{ $article->title }}" />
  <meta property="og:description"        content="{{ $article->description }}" />
  <meta property="og:image"              content="{{ $article->image_url  }}" />
  <meta property="og:image:secure_url"   content="{{ $article->image_url  }}" />
  <meta property="og:image:width"        content="{{ config('website.socialimagewidth') }}" />
  <meta property="og:image:height"       content="{{ config('website.socialimageheight') }}" />
  {{-- <meta property="fb:app_id"             content="{{ env('FACEBOOK_APP_ID') }}"> --}}
</head>
<body>

</body>
</html>