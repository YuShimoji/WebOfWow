<?php include 'header.php'; ?>
        <section class="articles">
            <h2>最新の記事</h2>

            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

            <article>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>

            <?php endwhile; else: ?>
                <p>記事はありません。</p>
            <?php endif; ?>
        </section>
<?php include 'footer.php'; ?>