<?php
class PhSolr {
  private $client;
  private $config;

  public function __construct(Solarium\Client $client, array $config) {
    $this->client = $client;
    $this->config = $config;
  }

  public function updateIndexPosts() {

  }

  public function updateIndexPages() {

  }

  public function updateIndexComments() {

  }

  public function search($query, $opt) {

  }
}
