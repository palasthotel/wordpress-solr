<?php
$id = str_replace('/', '-', $phsolr_document->id);
?><article id="<?php echo $id; ?>"
  class="<?php echo $id; ?> post type-post status-publish format-standard hentry category-uncategorized">

  <h1><a href="<?php echo $phsolr_document->url ?>"><?php echo $phsolr_document->title ?></a></h1>

  <div class="entry-content">
<?php
echo $phsolr_document->content;
?>
  </div>

</article>
