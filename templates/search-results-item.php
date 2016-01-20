<?php
/**
 * In the loop so use default post functions
 * @var \Solarium\QueryType\Select\Result\DocumentInterface $document
 */
?>
<article id="<?php the_ID(); ?>"  <?php post_class(); ?>>

  <h1><a href="<?php echo get_the_permalink(); ?>"><?php the_title(); ?></a></h1>

  <div class="entry-content"><?php the_content(); ?></div>

</article>
