<?php

function getAverageColor($img, $default='eee'){
    if(@exif_imagetype($img)) { // CHECK IF IT IS AN IMAGE
    $type = getimagesize($img)[2]; // GET TYPE
    if ($type === 1) { // GIF
      $image = imagecreatefromgif($img);
      // IF IMAGE IS TRANSPARENT (alpha=127) RETURN fff FOR WHITE
      if (imagecolorsforindex($image, imagecolorstotal($image)-1)['alpha'] == 127) return 'fff';
    } else if ($type === 2) { // JPG
      $image = imagecreatefromjpeg($img);
    } else if ($type === 3) { // PNG
      $image = imagecreatefrompng($img);
      // IF IMAGE IS TRANSPARENT (alpha=127) RETURN fff FOR WHITE
      if ((imagecolorat($image, 0, 0) >> 24) & 0x7F === 127) return 'fff';
    } else { // NO CORRECT IMAGE TYPE (GIF, JPG or PNG)
      return $default;
    }
  } else { // NOT AN IMAGE
    return $default;
  }
  $newImg = imagecreatetruecolor(1, 1); // FIND DOMINANT COLOR
  imagecopyresampled($newImg, $image, 0,0,0,0,1,1, imagesx($image), imagesy($image));
  return dechex(imagecolorat($newImg, 0, 0)); // RETURN HEX COLOR
}

$url = 'http://localhost:3000/api/get_article_list_and_view';

// Create a new cURL resource
$ch = curl_init($url);

// Setup request to send json via POST
$data = array(
	'user_id' => '1',
	'start_index' => '1',
	'number_of_article' => '10'
);
$payload = json_encode($data);

// Attach encoded JSON string to the POST fields
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

// Set the content type to application/json
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

// Return response instead of outputting
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the POST request
$articles = json_decode(curl_exec($ch));

// Close cURL resource
curl_close($ch);

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Title -->
	<title>Cypher</title>

	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">

	<!-- Plugins -->
	<script src="https://cdn.tailwindcss.com"></script>

	<!-- Styles -->
	<link rel="stylesheet" type="text/css" href="./css/styles.css">
</head>

<body>

	<!-- App -->
	<div class="app">
		<!-- Header -->
		<header class="header">
			<ul class="categories">
				<li class="categories__plus"><a href="#"><img src="./img/plus-icon.svg" alt=""></a></li>
				<li><a href="#">For You</a></li>
				<li><a href="#">Coding</a></li>
				<li><a href="#">Business</a></li>
				<li><a href="#">Other Categories</a></li>
			</ul>
		</header>

		<!-- Content -->
		<div class="content i-wrap">
			<div class="blog-post-wrap container flex flex-wrap">

				<?php foreach ($articles as $k => $article) : ?>

					<?php
						$bgColor = '#'.getAverageColor($article->image_url);
					?>

					<div class="w-full flex justify-center">
						<a href="#<?= $article->article_id ?>" class="blog-post inline-flex flex-wrap justify-between">
							<span style="background: linear-gradient(206.14deg, <?= $bgColor; ?>  0%, #4F4D55 145.34%);"></span>
							<div class="blog-post__left">
								<div class="blog-post__left__header flex items-center mb-4">
									<div class="blog-post__left__header__img">
										<img src="<?= $article->author_pfp ?>" alt="">
									</div>
									<div class="pl-3">
										<h2 class="mb-1"><?= $article->article_title ?></h2>
										<div class="flex blog-post__left__header__author">
											<span><?= $article->author_name ?></span> <img src="./img/lock-icon.svg" alt="" class="ml-2.5">
										</div>
									</div>
								</div>

								<ul class="categories mb-4">
									<?php foreach (explode(', ', $article->tags) as $tag) : ?>
										<li><span><?= $tag ?></span></li>
									<?php endforeach; ?>
								</ul>

								<div class="blog-post__left__meta flex mb-2.5">
									<div class="mr-3"><?= date('F d, Y', strtotime($article->date_posted)) ?></div>
									<!-- <div>5 min read</div> -->
								</div>

								<div class="blog-post__left__stock">
									<?= $article->total_invested ?>T Invested
								</div>
							</div>

							<div class="blog-post__right" style="background-image: url('<?= $article->image_url ?>')">&nbsp;
								<span style="background: linear-gradient(206.14deg, <?= $bgColor; ?> 0%, #4F4D55 145.34%);"></span>
							</div>
						</a>
					</div>

				<?php endforeach; ?>

				<!-- <div class="w-full flex justify-center">
					<a href="#" class="blog-post inline-flex flex-wrap justify-between">
						<span style="background: linear-gradient(206.14deg, #CEB9C0 0%, #4F4D55 145.34%);"></span>
						<div class="blog-post__left">
							<div class="blog-post__left__header flex items-center mb-4">
								<div class="blog-post__left__header__img">
									<img src="./img/dynamic/profile-1.png" alt="">
								</div>
								<div class="pl-3">
									<h2 class="mb-1">Why Python is The Future</h2>
									<div class="flex blog-post__left__header__author">
										<span>Ephraim Jones</span> <img src="./img/lock-icon.svg" alt="" class="ml-2.5">
									</div>
								</div>
							</div>

							<ul class="categories mb-4">
								<li><span>For You</span></li>
								<li><span>Coding</span></li>
							</ul>

							<div class="blog-post__left__meta flex mb-2.5">
								<div class="mr-3">January 3, 2021</div>
								<div>5 min read</div>
							</div>

							<div class="blog-post__left__stock">
								10025T Invested
							</div>
						</div>

						<div class="blog-post__right" style="background-image: url('./img/dynamic/post-1.png')">&nbsp;
							<span style="background: linear-gradient(206.14deg, #CEB9C0 0%, #4F4D55 145.34%);"></span>
						</div>
					</a>
				</div> -->

				<!-- <div class="w-full flex justify-center">
					<a href="#" class="blog-post inline-flex flex-wrap justify-between">
						<span style="background: linear-gradient(206.14deg, #B6D0E9 0%, #4B4857 145.34%);"></span>
						<div class="blog-post__left">
							<div class="blog-post__left__header flex items-center mb-4">
								<div class="blog-post__left__header__img">
									<img src="./img/dynamic/profile-2.png" alt="">
								</div>
								<div class="pl-3">
									<h2 class="mb-1">Super Chewy Cookies Recipe</h2>
									<div class="flex blog-post__left__header__author">
										<span>Eliza Mae</span> <img src="./img/lock-icon.svg" alt="" class="ml-2.5">
									</div>
								</div>
							</div>

							<ul class="categories mb-4">
								<li><span>For You</span></li>
								<li><span>Baking</span></li>
							</ul>

							<div class="blog-post__left__meta flex mb-2.5">
								<div class="mr-3">January 21, 2021</div>
								<div>2 min read</div>
							</div>

							<div class="blog-post__left__stock">
								7342T Invested
							</div>
						</div>

						<div class="blog-post__right" style="background-image: url('./img/dynamic/post-2.png')">&nbsp;
							<span style="background: linear-gradient(206.14deg, #B6D0E9 0%, #4B4857 145.34%);"></span>
						</div>
					</a>
				</div>

				<div class="w-full flex justify-center">
					<a href="#" class="blog-post inline-flex flex-wrap justify-between">
						<span style="background: linear-gradient(206.14deg, #C7CEB9 0%, #4F4D55 145.34%);"></span>
						<div class="blog-post__left">
							<div class="blog-post__left__header flex items-center mb-4">
								<div class="blog-post__left__header__img">
									<img src="./img/dynamic/profile-3.png" alt="">
								</div>
								<div class="pl-3">
									<h2 class="mb-1">The Go-To-Market Guide</h2>
									<div class="flex blog-post__left__header__author">
										<span>Cecilia Hong</span> <img src="./img/lock-icon.svg" alt="" class="ml-2.5">
									</div>
								</div>
							</div>

							<ul class="categories mb-4">
								<li><span>For You</span></li>
								<li><span>Business</span></li>
							</ul>

							<div class="blog-post__left__meta flex mb-2.5">
								<div class="mr-3">January 7, 2021</div>
								<div>10 min read</div>
							</div>

							<div class="blog-post__left__stock">
								8961T Invested
							</div>
						</div>

						<div class="blog-post__right" style="background-image: url('./img/dynamic/post-3.png')">&nbsp;
							<span style="background: linear-gradient(206.14deg, #C7CEB9 0%, #4F4D55 145.34%);"></span>
						</div>
					</a>
				</div>

				<div class="w-full flex justify-center">
					<a href="#" class="blog-post inline-flex flex-wrap justify-between">
						<span style="background: linear-gradient(206.14deg, #F4C7DF 0%, #894F80 145.34%);"></span>
						<div class="blog-post__left">
							<div class="blog-post__left__header flex items-center mb-4">
								<div class="blog-post__left__header__img">
									<img src="./img/dynamic/profile-4.png" alt="">
								</div>
								<div class="pl-3">
									<h2 class="mb-1">The Rules of Digital Marketing</h2>
									<div class="flex blog-post__left__header__author">
										<span>Melissa Shen</span> <img src="./img/lock-icon.svg" alt="" class="ml-2.5">
									</div>
								</div>
							</div>

							<ul class="categories mb-4">
								<li><span>For You</span></li>
								<li><span class="mx-w-80">Marketing</span></li>
							</ul>

							<div class="blog-post__left__meta flex mb-2.5">
								<div class="mr-3">January 19, 2021</div>
								<div>2 min read</div>
							</div>

							<div class="blog-post__left__stock">
								9456T Invested
							</div>
						</div>

						<div class="blog-post__right" style="background-image: url('./img/dynamic/post-4.png')">&nbsp;
							<span style="background: linear-gradient(206.14deg, #F4C7DF 0%, #894F80 145.34%);"></span>
						</div>
					</a>
				</div>

				<div class="w-full flex justify-center">
					<a href="#" class="blog-post inline-flex flex-wrap justify-between">
						<span style="background: linear-gradient(206.14deg, #CDCFFF 0%, #052054 145.34%);"></span>
						<div class="blog-post__left">
							<div class="blog-post__left__header flex items-center mb-4">
								<div class="blog-post__left__header__img">
									<img src="./img/dynamic/profile-5.png" alt="">
								</div>
								<div class="pl-3">
									<h2 class="mb-1">Building Muscle The Right Way</h2>
									<div class="flex blog-post__left__header__author">
										<span>Darren Jones</span> <img src="./img/lock-icon.svg" alt="" class="ml-2.5">
									</div>
								</div>
							</div>

							<ul class="categories mb-4">
								<li><span>For You</span></li>
								<li><span>Fitness</span></li>
							</ul>

							<div class="blog-post__left__meta flex mb-2.5">
								<div class="mr-3">January 24, 2021</div>
								<div>2 min read</div>
							</div>

							<div class="blog-post__left__stock">
								11275T Invested
							</div>
						</div>

						<div class="blog-post__right" style="background-image: url('./img/dynamic/post-5.png')">&nbsp;
							<span style="background: linear-gradient(206.14deg, #CDCFFF 0%, #052054 145.34%);"></span>
						</div>
					</a>
				</div> -->

			</div>
		</div>

		<!-- Footer -->
		<footer class="footer">
			<div class="container">
				<div class="footer__controls">
					<ul class="flex justify-center items-center">
						<li><a href="#"><img src="./img/home-icon.svg" alt=""></a></li>
						<li><a href="#"><img src="./img/search-icon.svg" alt=""></a></li>
						<li><a href="#" class="active"><img src="./img/plus-white-icon.svg" alt=""></a></li>
						<li><a href="#"><img src="./img/notification-icon.svg" alt=""></a></li>
						<li><a href="#"><img src="./img/profile-icon.svg" alt=""></a></li>
					</ul>
				</div>
			</div>
		</footer>

	</div>

	<!-- Plugins -->
	<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

	<!-- Script -->
	<script src="./js/main.js"></script>

</body>

</html>