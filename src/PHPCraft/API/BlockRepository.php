<?php

namespace PHPCraft\API;

class BlockRepository {
	public $BlockProviders = [];

	public function __construct() {
		$this->registerBlockProviders();
	}

	public function registerBlockProviders() {
//	$this->registerBlockProvider(new GrassBlockProvider());
	}

	public function getBlockProvider($id) {
		return $this->BlockProviders[$id];
	}

	public function registerBlockProvider($provider) {
		$this->BlockProviders[$provider->id] = $provider;
	}
}
