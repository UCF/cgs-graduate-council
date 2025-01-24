<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
?>

		</div><!-- .site-content -->
</div><!-- .site-inner -->

		<footer id="colophon" class="site-footer" role="contentinfo">
            <div class="footer-inner">
                <div class="footer-content">
                    <?php if ( has_nav_menu( 'primary' ) ) : ?>
                        <nav class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Footer Primary Menu', 'twentysixteen' ); ?>">
                            <?php
                                wp_nav_menu( array(
                                    'theme_location' => 'primary',
                                    'menu_class'     => 'primary-menu',
                                 ) );
                            ?>
                        </nav><!-- .main-navigation -->
                    <?php endif; ?>
                    <div class="footer-links">
                        <b>External Resources and Affiliates</b>
                        <div>
                            <a href="http://facultysenate.ucf.edu/">Faculty Senate</a> | 
                            <a href="http://bot.ucf.edu/">UCF Board of Trustees</a> | 
                            <a href="http://provost.ucf.edu/">Office of the Provost President</a> | 
                            <a href="http://www.flbog.edu/">Florida Board of Governors</a> | 
                            <a href="http://cgsnet.org/">Council of Graduate Schools</a> | 
                            <a href="http://www.graduatecatalog.ucf.edu/">Graduate Catalog</a>
                        </div>
                        <br>
                        <div id="Copyrights">
                            <?= get_field('gs_footer_details', 'option') ?>
                            <strong>&copy; <?= date("Y") ?> University of Central Florida</strong><br>
                            <a href="mailto:<?= get_field('gs_footer_contact_email', 'option') ?>">Contact Webmaster</a>
                        </div>
                        <!--<span class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></span>-->
                    </div><!-- .site-info -->
                </div>
            </div>
		</footer><!-- .site-footer -->
</div><!-- .site -->

<?php wp_footer(); ?>
</body>
</html>
