<?php
/**
 * In the loop so use default post functions
 */
?>
<article id="<?php the_ID(); ?>"  class="post type-post status-publish">

  <h1><a href="<?php echo get_the_permalink(); ?>"><?php the_title(); ?></a></h1>

  <div class="entry-content"><?php the_content(); ?></div>

</article>
