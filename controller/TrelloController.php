<?php

namespace Scrum\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class TrelloController
{
    public function indexAction(Request $request, Application $app)
    {
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
		        $formBuilder = $app['form.factory']->createNamedBuilder($i, 'form');

				$formBuilder->add('public', 'checkbox', array(
				    'label'    => $list->name,
				    'value'	   => $list->id,
				    'required' => false,
				));	

				$builder->add($formBuilder);

				$i++;
			}
			$form = $builder->getForm();

			// return $app['twig']->render('list.twig', array('form' => $form->createView()));
			return $app['twig']->render('list.twig', array('form' => $form->createView()));
		}

		return $app['twig']->render('list.twig', array('form' => $form->createView()));
    }

    public function listAction(Request $request, Application $app)
    {
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

			if ($difficulty > 0) {
				$item->difficulty = $difficulty;

				// enlève la difficulté du nom
				$item->name = $title;

				$list[$item->idList][$item->id] = $item;
			}
		}

		$listCards = array();
		foreach ($_REQUEST['form'] as $data) {
			if (isset($data['public']) && isset($list[$data['public']])) {
				$listCards = $list[$data['public']];
			}
		}

		// propose de downloader le rendu html

     	return $app['twig']->render('extract.twig', array('listCards' => $listCards));
    }
}
   