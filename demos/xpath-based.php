<?php

	use symphony\Actors\Actor;
	use symphony\Actors\ExecutionIterator;

	require_once '../vendor/autoload.php';

	abstract class GetData implements Actor {
		protected $input;
		protected $output;
		protected $xpath;
		protected $done;

		public function __construct(StdClass $input, DOMDocument $output) {
			$this->input = $input;
			$this->output = $output;
			$this->xpath = new DOMXPath($output);
			$this->done = false;
		}

		public function executable() {
			return true;
		}

		public function executed() {
			return $this->done;
		}

		public function ready($final = false) {
			return true;
		}
	}

	class GetArticles extends GetData {
		public function execute($final = false) {
			$input = $this->input;
			$output = $this->output;

			$results = $output->createElement('get-articles');
			$output->documentElement->appendChild($results);

			foreach ($input->articles as $handle => $article) {
				$entry = $output->createElement('entry');
				$entry->setAttribute('handle', $handle);
				$results->appendChild($entry);

				foreach ($article as $name => $value) {
					$field = $output->createElement($name);
					$entry->appendChild($field);

					if (is_array($value) || is_object($value)) {
						foreach ($value as $id) {
							$item = $output->createElement('item');
							$item->setAttribute('id', $id);
							$field->appendChild($item);
						}
					}

					else {
						$text = $output->createTextNode($value);
						$field->appendChild($text);
					}
				}
			}

			return $this->done = true;
		}
	}

	class GetImages extends GetData {
		const QUERY = '//images/item/@id';

		public function ready($final = false) {
			return $this->xpath->evaluate('boolean(' . self::QUERY . ')');
		}

		public function execute($final = false) {
			$input = $this->input;
			$output = $this->output;

			$results = $output->createElement('get-images');
			$output->documentElement->appendChild($results);

			foreach ($this->xpath->query(self::QUERY) as $item) {
				if (isset($item->nodeValue) === false) continue;
				if (isset($input->images->{$item->nodeValue}) === false) continue;

				$entry = $output->createElement('entry');
				$entry->setAttribute('id', $item->nodeValue);
				$results->appendChild($entry);

				foreach ($input->images->{$item->nodeValue} as $name => $value) {
					$field = $output->createElement($name);
					$entry->appendChild($field);

					$text = $output->createTextNode($value);
					$field->appendChild($text);
				}
			}

			return $this->done = true;
		}
	}

	class DoNothing extends GetData {
		public function ready($final = false) {
			return false;
		}

		public function execute($final = false) {
			return $this->done = true;
		}
	}

	// Load source data:
	$input = json_decode(file_get_contents('xpath-based.json'));

	// Prepare output document:
	$output = new DOMDocument();
	$output->formatOutput = true;
	$output->load('xpath-based.xml');

	$actors = new ExecutionIterator([
		new GetImages($input, $output),
		new GetArticles($input, $output),
		new DoNothing($input, $output)
	]);

	foreach ($actors->execute() as $actor => $result) {
		if ($result instanceof Exception) {
			// Log the exception without interrupting execution...
		}
	}

	echo '<pre>', htmlentities($output->saveXML());