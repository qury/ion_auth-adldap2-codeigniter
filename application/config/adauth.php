<?php
/*
 * The MIT License
 *
 * Copyright 2017 ferenc.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

// Site name displayed on login screen
$config['adauth']['site'] = 'AD Auth Test';

// Success page
$config['adauth']['success'] = 'welcome/index';

// Unauthorized access page
$config['adauth']['unauthorized'] = 'welcome/index';

// Update existing user with properties from active directory?
// If set to true the following fields will get updated:
//	'first_name'
//	'last_name'
//	'company'
//	'email'
//	'phone'
$config['adauth']['update_existing_user'] = TRUE;

// Table containing extra user preferences
// This table will get created by migrations.
$config['tables']['user_extra'] = 'user_pref';

// The default theme properties for the login screen and for the users
// These are also used as defaults for new users.
$config['adauth']['bootstrap_theme'] = 'bootstrap.min.css';

// Navbar style: default or inverse
$config['adauth']['bootstrap_navbar'] = 'default';

// The default domain that is being displayed on the login screen
// This is also used for providing a dfault domain for locally created users.
$config['adauth']['default_domain'] = 'debian';

// The default value that is being pre-populated when creating a local user.
$config['adauth']['default_company'] = 'Test Company';

/**
 * Domains we will try to authenticate against
 */
$config['domain']['debian'] = 'debian.localhost';


/**
 * Available bootstrap compatible CSS themes  (from assets/css)
 */
$config['theme']['bootstrap.lumen.css']		 = 'bootstrap.lumen.css';
$config['theme']['bootstrap.paper.css']		 = 'bootstrap.paper.css';
$config['theme']['bootstrap.cerulean.css']	 = 'bootstrap.cerulean.css';
$config['theme']['bootstrap.cosmo.css']		 = 'bootstrap.cosmo.css';
$config['theme']['bootstrap.cyborg.css']	 = 'bootstrap.cyborg.css';
$config['theme']['bootstrap.darkly.css']	 = 'bootstrap.darkly.css';
$config['theme']['bootstrap.flatly.css']	 = 'bootstrap.flatly.css';
$config['theme']['bootstrap.journal.css']	 = 'bootstrap.journal.css';
$config['theme']['bootstrap.min.css']		 = 'bootstrap.min.css';
$config['theme']['bootstrap.sandstone.css']	 = 'bootstrap.sandstone.css';
$config['theme']['bootstrap.simplex.css']	 = 'bootstrap.simplex.css';
$config['theme']['bootstrap.slate.css']		 = 'bootstrap.slate.css';
$config['theme']['bootstrap.spacelab.css']	 = 'bootstrap.spacelab.css';
$config['theme']['bootstrap.superhero.css']	 = 'bootstrap.superhero.css';
$config['theme']['bootstrap.united.css']	 = 'bootstrap.united.css';
$config['theme']['bootstrap.yeti.css']		 = 'bootstrap.yeti.css';


