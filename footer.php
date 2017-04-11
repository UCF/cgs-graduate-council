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
                        <?php
                            /**
                             * Fires before the twentysixteen footer text for footer customization.
                             *
                             * @since Twenty Sixteen 1.0
                             */
                            do_action( 'twentysixteen_credits' );
                        ?>
                        <div id="Copyrights" style="text-align: center; font-size: 11px; line-height: 13px">
                            <strong>&copy; 2016 University of Central Florida</strong><br>
                            Graduate Council 407-823-3567. Site maintained by College of Graduate Studies, <br>
                            Millican Hall 230, PO Box 160112, Orlando, FL 32816-0112. <a href="mailto:grad_web@ucf.edu">Webmaster</a>
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
