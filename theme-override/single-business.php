<?php get_header(); ?>

<div class="content">
                              
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
  
    <div class="posts">
  
      <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <div class="post-inner">

        <div class="post-header">
          
            <h2 class="post-title"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
            
        </div> <!-- /post-header -->
                                                                    
        <div class="post-content">
                                                                                                                                              
          <?php the_content(); ?>
              
          <?php wp_link_pages(); ?>
                        
        </div> <!-- /post-content -->
                    
        <div class="clear"></div>
        
      </div> <!-- /post-inner -->
      
    </div> <!-- /post -->
    
  </div> <!-- /posts -->
  
  <?php //comments_template( '', true ); ?> 
        
  <?php endwhile; else: ?>

    <p><?php _e("We couldn't find any posts that matched your query. Please try again.", "wilson"); ?></p>
  
  <?php endif; ?>   

<?php get_footer(); ?>