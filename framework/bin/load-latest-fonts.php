#!/usr/bin/env php

<?php

/**
 * Download latest font packs from their sources.
 */

function get_libs_dir() {
	return dirname(__FILE__) . '/../static/libs/';
}

$packs = array(
	array(
		'name' => 'entypo',
		'github-repo' => 'danielbruce/entypo',
		'css-file' => 'font/entypo.css',

		'fonts' => array(
			'font/entypo.eot',
			'font/entypo.svg',
			'font/entypo.ttf',
			'font/entypo.woff'
		),

		'replace' => array(
			array(
				'from' => "icon-",
				'to' => "entypo-"
			),

			array(
				'from' => "url('entypo",
				'to' => "url('../fonts/entypo"
			),
		)
	),

	'linearicons' => array(
		'name' => 'lnr',
		'css-file' => 'https://cdn.linearicons.com/free/1.0.0/icon-font.min.css',
	),

	'typicons' => array(
		'name' => 'typcn',
		'github-repo' => 'stephenhutchings/typicons.font',

		'css-file' => 'src/font/typicons.css',

		'fonts' => array(
			'src/font/typicons.eot',
			'src/font/typicons.svg',
			'src/font/typicons.ttf',
			'src/font/typicons.woff'
		),

		'replace' => array(
			array(
				'from' => "url('typicons",
				'to' => "url('../fonts/typicons"
			)
		)
	),

	'font-awesome' => array(
		'name' => 'font-awesome',
		'github-repo' => 'FortAwesome/Font-Awesome',
		'css-file' => 'css/font-awesome.min.css',
		'css-file-output' => 'font-awesome.min.css',
		'fonts' => array(
			'fonts/FontAwesome.otf',
			'fonts/fontawesome-webfont.eot',
			'fonts/fontawesome-webfont.svg',
			'fonts/fontawesome-webfont.ttf',
			'fonts/fontawesome-webfont.woff',
			'fonts/fontawesome-webfont.woff2'
		)
	)
);

foreach ($packs as $pack) {
	create_pack_directory($pack);

	download_css($pack);
	download_fonts($pack);
	perform_replacements($pack);
}

function create_pack_directory($pack) {
	if (file_exists(get_libs_dir() . $pack['name'])) return;

	mkdir(get_libs_dir() . $pack['name']);
	mkdir(get_libs_dir() . $pack['name'] . '/css');
	mkdir(get_libs_dir() . $pack['name'] . '/fonts');

	echo 'install ' . $pack['name'] . "\n";
}

function download_css($pack) {
	if (! isset($pack['css-file'])) { return; }

	$url = github_or_network_for($pack['css-file'], $pack);

	$file_name = $pack['name'] . '.css';

	if (isset($pack['css-file-output'])) {
		$file_name = $pack['css-file-output'];
	}

	download_file(
		$url,
		// __DIR__ . '/static/css/' . $pack['name'] . '.css'
		get_libs_dir() . $pack['name'] . '/css/' . $file_name
	);
}

function download_fonts($pack) {
	if (! isset($pack['fonts'])) { return; }

	foreach ($pack['fonts'] as $font) {
		download_file(
			github_or_network_for($font, $pack),
			// __DIR__ . '/static/fonts/' . basename( $font )
			get_libs_dir() . $pack['name'] . '/fonts/' . basename( $font )
		);
	}
}

function perform_replacements($pack) {
	if (! isset($pack['replace'])) { return; }

	// $file_path = __DIR__ . '/static/css/' . $pack['name'] . '.css';
	$file_path = get_libs_dir() . $pack['name'] . '/css/' . $pack['name'] . '.css';

	$data = file_get_contents($file_path);

	foreach ($pack['replace'] as $recipe) {
		$data = str_replace(
			$recipe['from'],
			$recipe['to'],
			$data
		);
	}

	file_put_contents($file_path, $data);
}

function github_or_network_for($base_url, $pack) {
	if (! isset($pack['github-repo'])) { return $base_url; }

	return 'https://raw.githubusercontent.com/' .
		$pack['github-repo'] .
		'/master/' . $base_url;
}

function download_file($url, $destination) {
	echo 'downloading ' . $destination . "\n";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt( $ch, CURLOPT_ENCODING, "UTF-8" );

	$data = curl_exec ($ch);
	$error = curl_error($ch);

	curl_close ($ch);

	$file = fopen($destination, "w+");
	fputs($file, $data);
	fclose($file);
}
