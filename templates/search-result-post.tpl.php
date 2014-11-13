<article id="post-<?php echo explode('/',$phsolr_document->id)[1]; ?>"
  class="post-19 post type-post status-publish format-standard hentry category-uncategorized">

  <header class="entry-header">
    <h1 class="entry-title">
      <a href="<?php echo $phsolr_document->url ?>"><?php echo $phsolr_document->title ?></a>
    </h1>
    <div class="entry-meta">
      <time class="entry-date" datetime="<?php echo $phsolr_document->date ?>"><?php echo explode('T',$phsolr_document->date)[0] ?></time>

    </div>
  </header>

  <div class="entry-content">
<?php
echo $phsolr_document->content;
?>
  </div>

</article>
