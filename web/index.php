<?php

// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

use Silex\Provider\FormServiceProvider;
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/../views',
));
$app->register(new FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
));

$app->get('/', function (Request $request) use ($app) {
	// some default data for when the form is displayed the first time

	$form = $app['form.factory']->createBuilder('form')
		->add('file', 'file', array(
           'attr' => array(
                'class' => 'form-control input-box filestyle',
                'data-buttonbefore' => 'true',
                'data-buttontext' => 'Trello',
                'style' => 'position: absolute; clip: rect(0px, 0px, 0px, 0px);'
            ),
           'label' => false,
            'required' => true,
		))
		->getForm();

	return $app['twig']->render('trello.html.twig', array('form' => $form->createView()));
});

$app->post('/', function (Request $request) use ($app) {
	// some default data for when the form is displayed the first time
	$form = $app['form.factory']->createBuilder('form')
		->add('file', 'file')
		->getForm();
		
	$request = $app['request'];

	$form->handleRequest($request);

	if ($form->isValid()) {
		$file = $form['file']->getData();

		$contents = json_decode(file_get_contents($file->getPathname()));

		$file->move(__DIR__, 'trello.json');

		$builder = $app['form.factory']->createBuilder('form');
		$i = 0;
		
		foreach ($contents->lists as $list) {
			if ($list->closed) {
				continue;
			}			

	        // $formBuilder = $app['form.factory']->createNamedBuilder($i, 'form');

			$builder->add('column_'.$i, 'checkbox', array(
			    'label'    => $list->name,
			    'value'	   => $list->id,
			    'required' => false,
			));	

			// $builder->add($formBuilder);

			$i++;
		}
		$form = $builder->getForm();

		// return $app['twig']->render('list.twig', array('form' => $form->createView()));
		return $app['twig']->render('list.html.twig', array('forms' => $form->createView()));
	}

	return $app['twig']->render('list.html.twig', array('forms' => $form->createView()));
});

$app->post('/list', function (Request $request) use ($app) {
	// some default data for when the form is displayed the first time
	$contents = json_decode(file_get_contents('trello.json'));

	$list = array();

	foreach ($contents->cards as $item) {
		if ($item->closed) {
			continue;
		}

		$difficulty = 0;
		$title = $item->name;

		// extract the difficulty from the name in ()
		preg_match("/^\((\d+.?\d*)\)\s(.+)/i", $item->name, $matches);

		if (!empty($matches[1])) {
			$difficulty = $matches[1];
			$title = $matches[2];
		}

		$item->difficulty = $difficulty;

		// enlève la difficulté du nom
		$item->name = $title;

		$list[$item->idList][$item->id] = $item;
	}
	$listCards = array();

	// echo '<pre>';
	// var_dump($_REQUEST['form']); die();

	foreach ($_REQUEST['form'] as $key => $data) {
		if ($key != '_token' && isset($list[$data])) {
			$listCards += $list[$data];
		}
	}

 	return $app['twig']->render('extract.twig', array('listCards' => $listCards, 'name' => $contents->name));
});

$app['debug'] = true;

$app->run();