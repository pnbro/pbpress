<?php pb_theme_header(); ?>

	<!-- Header -->
	<header class="masthead">
		<div class="container">
			<div class="intro-text">
				<div class="intro-lead-in"><?= __('Welcome To Our Studio!') ?></div>
				<div class="intro-heading text-uppercase"><?=__("It's Nice To Meet You")?></div>
				<a class="btn btn-primary btn-xl text-uppercase js-scroll-trigger" href="#services"><?=__('Tell Me More')?></a>
			</div>
		</div>
	</header>

	<!-- Services -->
	<section class="page-section" id="services">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 text-center">
					<h2 class="section-heading text-uppercase"><?= __('Services') ?></h2>
					<h3 class="section-subheading text-muted"><?= __('Lorem ipsum dolor sit amet consectetur.') ?></h3>
				</div>
			</div>
			<div class="row text-center">
				<div class="col-md-4">
					<span class="fa-stack fa-4x">
						<i class="fas fa-circle fa-stack-2x text-primary"></i>
						<i class="fas fa-shopping-cart fa-stack-1x fa-inverse"></i>
					</span>
					<h4 class="service-heading"><?= __('E-Commerce') ?></h4>
					<p class="text-muted"><?= __('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Minima maxime quam architecto quo inventore harum ex magni, dicta impedit.') ?></p>
				</div>
				<div class="col-md-4">
					<span class="fa-stack fa-4x">
						<i class="fas fa-circle fa-stack-2x text-primary"></i>
						<i class="fas fa-laptop fa-stack-1x fa-inverse"></i>
					</span>
					<h4 class="service-heading"><?= __('Responsive Design') ?></h4>
					<p class="text-muted"><?= __('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Minima maxime quam architecto quo inventore harum ex magni, dicta impedit.') ?></p>
				</div>
				<div class="col-md-4">
					<span class="fa-stack fa-4x">
						<i class="fas fa-circle fa-stack-2x text-primary"></i>
						<i class="fas fa-lock fa-stack-1x fa-inverse"></i>
					</span>
					<h4 class="service-heading"><?= __('Web Security') ?></h4>
					<p class="text-muted"><?= __('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Minima maxime quam architecto quo inventore harum ex magni, dicta impedit.') ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- Portfolio Grid -->
	<section class="bg-light page-section" id="portfolio">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 text-center">
					<h2 class="section-heading text-uppercase"><?= __('Portfolio') ?></h2>
					<h3 class="section-subheading text-muted"><?= __('Lorem ipsum dolor sit amet consectetur.') ?></h3>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4 col-sm-6 portfolio-item">
					<a class="portfolio-link" data-toggle="modal" href="#portfolioModal1">
						<div class="portfolio-hover">
							<div class="portfolio-hover-content">
								<i class="fas fa-plus fa-3x"></i>
							</div>
						</div>
						<img class="img-fluid" src="<?=pb_current_theme_url()?>img/portfolio/01-thumbnail.jpg" alt="">
					</a>
					<div class="portfolio-caption">
						<h4><?= __('Threads') ?></h4>
						<p class="text-muted"><?= __('Illustration') ?></p>
					</div>
				</div>
				<div class="col-md-4 col-sm-6 portfolio-item">
					<a class="portfolio-link" data-toggle="modal" href="#portfolioModal1">
						<div class="portfolio-hover">
							<div class="portfolio-hover-content">
								<i class="fas fa-plus fa-3x"></i>
							</div>
						</div>
						<img class="img-fluid" src="<?=pb_current_theme_url()?>img/portfolio/02-thumbnail.jpg" alt="">
					</a>
					<div class="portfolio-caption">
						<h4><?= __('Explore') ?></h4>
						<p class="text-muted"><?= __('Graphic Design') ?></p>
					</div>
				</div>
				<div class="col-md-4 col-sm-6 portfolio-item">
					<a class="portfolio-link" data-toggle="modal" href="#portfolioModal1">
						<div class="portfolio-hover">
							<div class="portfolio-hover-content">
								<i class="fas fa-plus fa-3x"></i>
							</div>
						</div>
						<img class="img-fluid" src="<?=pb_current_theme_url()?>img/portfolio/03-thumbnail.jpg" alt="">
					</a>
					<div class="portfolio-caption">
						<h4><?= __('Finish') ?></h4>
						<p class="text-muted"><?= __('Identity') ?></p>
					</div>
				</div>
				<div class="col-md-4 col-sm-6 portfolio-item">
					<a class="portfolio-link" data-toggle="modal" href="#portfolioModal1">
						<div class="portfolio-hover">
							<div class="portfolio-hover-content">
								<i class="fas fa-plus fa-3x"></i>
							</div>
						</div>
						<img class="img-fluid" src="<?=pb_current_theme_url()?>img/portfolio/04-thumbnail.jpg" alt="">
					</a>
					<div class="portfolio-caption">
						<h4><?= __('Lines') ?></h4>
						<p class="text-muted"><?= __('Branding') ?></p>
					</div>
				</div>
				<div class="col-md-4 col-sm-6 portfolio-item">
					<a class="portfolio-link" data-toggle="modal" href="#portfolioModal1">
						<div class="portfolio-hover">
							<div class="portfolio-hover-content">
								<i class="fas fa-plus fa-3x"></i>
							</div>
						</div>
						<img class="img-fluid" src="<?=pb_current_theme_url()?>img/portfolio/05-thumbnail.jpg" alt="">
					</a>
					<div class="portfolio-caption">
						<h4><?= __('Southwest') ?></h4>
						<p class="text-muted"><?= __('Website Design') ?></p>
					</div>
				</div>
				<div class="col-md-4 col-sm-6 portfolio-item">
					<a class="portfolio-link" data-toggle="modal" href="#portfolioModal1">
						<div class="portfolio-hover">
							<div class="portfolio-hover-content">
								<i class="fas fa-plus fa-3x"></i>
							</div>
						</div>
						<img class="img-fluid" src="<?=pb_current_theme_url()?>img/portfolio/06-thumbnail.jpg" alt="">
					</a>
					<div class="portfolio-caption">
						<h4><?= __('Window') ?></h4>
						<p class="text-muted"><?= __('Photography') ?></p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- About -->
	<section class="page-section" id="about">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 text-center">
					<h2 class="section-heading text-uppercase"><?= __('About') ?></h2>
					<h3 class="section-subheading text-muted"><?= __('Lorem ipsum dolor sit amet consectetur.') ?></h3>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<ul class="timeline">
						<li>
							<div class="timeline-image">
								<img class="rounded-circle img-fluid" src="<?=pb_current_theme_url()?>img/about/1.jpg" alt="">
							</div>
							<div class="timeline-panel">
								<div class="timeline-heading">
									<h4><?= __('2009-2011') ?></h4>
									<h4 class="subheading"><?= __('Our Humble Beginnings') ?></h4>
								</div>
								<div class="timeline-body">
									<p class="text-muted"><?= __('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt ut voluptatum eius sapiente, totam reiciendis temporibus qui quibusdam, recusandae sit vero unde, sed, incidunt et ea quo dolore laudantium consectetur!') ?></p>
								</div>
							</div>
						</li>
						<li class="timeline-inverted">
							<div class="timeline-image">
								<img class="rounded-circle img-fluid" src="<?=pb_current_theme_url()?>img/about/2.jpg" alt="">
							</div>
							<div class="timeline-panel">
								<div class="timeline-heading">
									<h4><?= __('March 2011') ?></h4>
									<h4 class="subheading"><?= __('An Agency is Born') ?></h4>
								</div>
								<div class="timeline-body">
									<p class="text-muted"><?= __('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt ut voluptatum eius sapiente, totam reiciendis temporibus qui quibusdam, recusandae sit vero unde, sed, incidunt et ea quo dolore laudantium consectetur!') ?></p>
								</div>
							</div>
						</li>
						<li>
							<div class="timeline-image">
								<img class="rounded-circle img-fluid" src="<?=pb_current_theme_url()?>img/about/3.jpg" alt="">
							</div>
							<div class="timeline-panel">
								<div class="timeline-heading">
									<h4><?= __('December 2012') ?></h4>
									<h4 class="subheading"><?= __('Transition to Full Service') ?></h4>
								</div>
								<div class="timeline-body">
									<p class="text-muted"><?= __('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt ut voluptatum eius sapiente, totam reiciendis temporibus qui quibusdam, recusandae sit vero unde, sed, incidunt et ea quo dolore laudantium consectetur!') ?></p>
								</div>
							</div>
						</li>
						<li class="timeline-inverted">
							<div class="timeline-image">
								<img class="rounded-circle img-fluid" src="<?=pb_current_theme_url()?>img/about/4.jpg" alt="">
							</div>
							<div class="timeline-panel">
								<div class="timeline-heading">
									<h4><?= __('July 2014') ?></h4>
									<h4 class="subheading"><?= __('Phase Two Expansion') ?></h4>
								</div>
								<div class="timeline-body">
									<p class="text-muted"><?= __('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Sunt ut voluptatum eius sapiente, totam reiciendis temporibus qui quibusdam, recusandae sit vero unde, sed, incidunt et ea quo dolore laudantium consectetur!') ?></p>
								</div>
							</div>
						</li>
						<li class="timeline-inverted">
							<div class="timeline-image">
								<h4><?= __('Be Part') ?>
									<br><?= __('Of Our') ?>
									<br><?= __('Story!') ?></h4>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<!-- Team -->
	<section class="bg-light page-section" id="team">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 text-center">
					<h2 class="section-heading text-uppercase"><?= __('Our Amazing Team') ?></h2>
					<h3 class="section-subheading text-muted"><?= __('Lorem ipsum dolor sit amet consectetur.') ?></h3>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-4">
					<div class="team-member">
						<img class="mx-auto rounded-circle" src="<?=pb_current_theme_url()?>img/team/1.jpg" alt="">
						<h4><?= __('Kay Garland') ?></h4>
						<p class="text-muted"><?= __('Lead Designer') ?></p>
						<ul class="list-inline social-buttons">
							<li class="list-inline-item">
								<a href="#">
									<i class="fab fa-twitter"></i>
								</a>
							</li>
							<li class="list-inline-item">
								<a href="#">
									<i class="fab fa-facebook-f"></i>
								</a>
							</li>
							<li class="list-inline-item">
								<a href="#">
									<i class="fab fa-linkedin-in"></i>
								</a>
							</li>
						</ul>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="team-member">
						<img class="mx-auto rounded-circle" src="<?=pb_current_theme_url()?>img/team/2.jpg" alt="">
						<h4><?= __('Larry Parker') ?></h4>
						<p class="text-muted"><?= __('Lead Marketer') ?></p>
						<ul class="list-inline social-buttons">
							<li class="list-inline-item">
								<a href="#">
									<i class="fab fa-twitter"></i>
								</a>
							</li>
							<li class="list-inline-item">
								<a href="#">
									<i class="fab fa-facebook-f"></i>
								</a>
							</li>
							<li class="list-inline-item">
								<a href="#">
									<i class="fab fa-linkedin-in"></i>
								</a>
							</li>
						</ul>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="team-member">
						<img class="mx-auto rounded-circle" src="<?=pb_current_theme_url()?>img/team/3.jpg" alt="">
						<h4><?= __('Diana Pertersen') ?></h4>
						<p class="text-muted"><?= __('Lead Developer') ?></p>
						<ul class="list-inline social-buttons">
							<li class="list-inline-item">
								<a href="#">
									<i class="fab fa-twitter"></i>
								</a>
							</li>
							<li class="list-inline-item">
								<a href="#">
									<i class="fab fa-facebook-f"></i>
								</a>
							</li>
							<li class="list-inline-item">
								<a href="#">
									<i class="fab fa-linkedin-in"></i>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-8 mx-auto text-center">
					<p class="large text-muted"><?= __('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut eaque, laboriosam veritatis, quos non quis ad perspiciatis, totam corporis ea, alias ut unde.') ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- Clients -->
	<section class="py-5">
		<div class="container">
			<div class="row">
				<div class="col-md-3 col-sm-6">
					<a href="#">
						<img class="img-fluid d-block mx-auto" src="<?=pb_current_theme_url()?>img/logos/envato.jpg" alt="">
					</a>
				</div>
				<div class="col-md-3 col-sm-6">
					<a href="#">
						<img class="img-fluid d-block mx-auto" src="<?=pb_current_theme_url()?>img/logos/designmodo.jpg" alt="">
					</a>
				</div>
				<div class="col-md-3 col-sm-6">
					<a href="#">
						<img class="img-fluid d-block mx-auto" src="<?=pb_current_theme_url()?>img/logos/themeforest.jpg" alt="">
					</a>
				</div>
				<div class="col-md-3 col-sm-6">
					<a href="#">
						<img class="img-fluid d-block mx-auto" src="<?=pb_current_theme_url()?>img/logos/creative-market.jpg" alt="">
					</a>
				</div>
			</div>
		</div>
	</section>

	<!-- Contact -->
	<section class="page-section" id="contact">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 text-center">
					<h2 class="section-heading text-uppercase"><?= __('Contact Us') ?></h2>
					<h3 class="section-subheading text-muted"><?= __('Lorem ipsum dolor sit amet consectetur.') ?></h3>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<form id="contactForm" name="sentMessage" novalidate="novalidate">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<input class="form-control" id="name" type="text" placeholder="<?= __('Your Name *') ?>" required="required" data-validation-required-message="<?= __('Please enter your name.') ?>">
									<p class="help-block text-danger"></p>
								</div>
								<div class="form-group">
									<input class="form-control" id="email" type="email" placeholder="<?= __('Your Email *') ?>" required="required" data-validation-required-message="<?= __('Please enter your email address.') ?>">
									<p class="help-block text-danger"></p>
								</div>
								<div class="form-group">
									<input class="form-control" id="phone" type="tel" placeholder="<?= __('Your Phone *') ?>" required="required" data-validation-required-message="<?= __('Please enter your phone number.') ?>">
									<p class="help-block text-danger"></p>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<textarea class="form-control" id="message" placeholder="<?= __('Your Message *') ?>" required="required" data-validation-required-message="<?= __('Please enter a message.') ?>"></textarea>
									<p class="help-block text-danger"></p>
								</div>
							</div>
							<div class="clearfix"></div>
							<div class="col-lg-12 text-center">
								<div id="success"></div>
								<button id="sendMessageButton" class="btn btn-primary btn-xl text-uppercase" type="submit"><?= __('Send Message') ?></button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</section>


	<!-- Portfolio Modals -->

	<!-- Modal 1 -->
	<div class="portfolio-modal modal fade" id="portfolioModal1" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="close-modal" data-dismiss="modal">
					<div class="lr">
						<div class="rl"></div>
					</div>
				</div>
				<div class="container">
					<div class="row">
						<div class="col-lg-8 mx-auto">
							<div class="modal-body">
								<!-- Project Details Go Here -->
								<h2 class="text-uppercase"><?= __('Project Name') ?></h2>
								<p class="item-intro text-muted"><?= __('Lorem ipsum dolor sit amet consectetur.') ?></p>
								<img class="img-fluid d-block mx-auto" src="<?=pb_current_theme_url()?>img/portfolio/01-full.jpg" alt="">
								<p><?= __('Use this area to describe your project. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est blanditiis dolorem culpa incidunt minus dignissimos deserunt repellat aperiam quasi sunt officia expedita beatae cupiditate, maiores repudiandae, nostrum, reiciendis facere nemo!') ?></p>
								<ul class="list-inline">
									<li><?= __('Date: January 2017') ?></li>
									<li><?= __('Client: Threads') ?></li>
									<li><?= __('Category: Illustration') ?></li>
								</ul>
								<button class="btn btn-primary" data-dismiss="modal" type="button">
									<i class="fas fa-times"></i>
									<?= __('Close Project') ?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php pb_theme_footer(); ?> 