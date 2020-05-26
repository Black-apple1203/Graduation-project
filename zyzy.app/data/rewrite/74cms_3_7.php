<?php
	return array(
		'alias' => '74cms_3_7',
		'name' => '骑士人才系统3.7版URL',
		'explain' => '说明',
		'versions' => '1.0.0',
		'update_time' => '2016-09-09',
		'author' => '74cms',
		'suffix' => '.htm',
		'config_url' => array(
			'QS_index' => array (
				'rewrite' => 'index',
				'url_reg' => '/^index$/',
				'url' => 'home/index/index'
			),
			'QS_jobs' => array (
				'rewrite' => 'jobs/index',
				'url_reg' => '/^jobs\/index$/',
				'url' => 'home/jobs/index'
			),
			'QS_jobslist' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => '',
			),
			'QS_companylist' => array (
				'rewrite' => 'company/company-list-($key)-($trade)-($citycategory)-($nature)-($scale)-($page)',
				'url_reg' => '/^company\/company-list-(.*)\-(.*)\-(.*)\-(.*)\-(.*)\-(.*)$/',
				'url' => 'home/jobs/company_list?key=:1&trade=:2&citycategory=:3&nature=:4&scale=:5&p=:6',
			),
			'QS_jobsshow' => array (
				'rewrite' => 'jobs/jobs-show-($id)-($style)',
				'url_reg' => '/^jobs\/jobs\-show\-(.*)\-(.*)$/',
				'url' => 'home/jobs/jobs_show?id=:1&style=:2'
			),
			'QS_companyshow' => array (
				'rewrite' => 'company/company-show-($id)-($style)',
				'url_reg' => '/^company\/company\-show\-(.*)\-(.*)$/',
				'url' => 'home/jobs/com_show?id=:1&style=:2'
			),
			'QS_companyjobs' => array (
				'rewrite' => 'company/company-jobs-($id)-($page)-($style)',
				'url_reg' => '/^company\/company\-jobs\-(.*)\-(.*)\-(.*)$/',
				'url' => 'home/jobs/com_jobs_list?id=:1&p=:2&style=:3'
			),
			'QS_login' => array (
				'rewrite' => 'user/login',
				'url_reg' => '/^user\/login$/',
				'url' => 'home/members/login'
			),
			'QS_resume' => array (
				'rewrite' => 'resume/index',
				'url_reg' => '/^resume\/index$/',
				'url' => 'home/resume/index'
			),
			'QS_resumelist' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => ''
			),
			'QS_resumeshow' => array (
				'rewrite' => 'resume/resume-show-($id)',
				'url_reg' => '/^resume\/resume\-show\-(.*)$/',
				'url' => 'home/resume/resume_show?id=:1'
			),
			'QS_hrtools' => array (
				'rewrite' => 'hrtools/index',
				'url_reg' => '/^hrtools\/index$/',
				'url' => 'home/hrtools/index'
			),
			'QS_hrtoolslist' => array (
				'rewrite' => 'hrtools/hrtools-list-($id)',
				'url_reg' => '/^hrtools\/hrtools\-list\-(.*)$/',
				'url' => 'home/hrtools/hrtools_list?id=:1'
			),
			'QS_news' => array (
				'rewrite' => 'news/index',
				'url_reg' => '/^news\/index$/',
				'url' => 'home/news/index'
			),
			'QS_newslist' => array (
				'rewrite' => 'news/news-list-($id)-($key)-($page)',
				'url_reg' => '/^news\/news\-list\-(.*)\-(.*)\-(.*)$/',
				'url' => 'home/news/news_list?id=:1&key=:2&p=:3'
			),
			'QS_newsshow' => array (
				'rewrite' => 'news/news-show-($id)',
				'url_reg' => '/^news\/news\-show\-(\d+)$/',
				'url' => 'home/news/news_show?id=:1'
			),
			'QS_explainshow' => array (
				'rewrite' => 'explain/explain-show-($id)',
				'url_reg' => '/^explain\/explain\-show\-(.*)$/',
				'url' => 'home/explain/explain_show?id=:1'
			),
			'QS_noticelist' => array (
				'rewrite' => 'notice/notice-list-($id)-($page)',
				'url_reg' => '/^notice\/notice\-list\-(.*)\-(.*)$/',
				'url' => 'home/notice/index?id=:1&p=:2'
			),
			'QS_noticeshow' => array (
				'rewrite' => 'notice/notice-show-($id)',
				'url_reg' => '/^notice\/notice\-show\-(.*)$/',
				'url' => 'home/notice/notice_show?id=:1'
			),
			'QS_jobfairlist' => array (
				'rewrite' => 'jobfair/jobfair-list-($page)',
				'url_reg' => '/^jobfair\/jobfair\-list\-(.*)$/',
				'url' => 'jobfair/index/index?p=:1'
			),
			'QS_jobfairshow' => array (
				'rewrite' => 'jobfair/jobfair-show-($id)',
				'url_reg' => '/^jobfair\/jobfair\-show\-(.*)$/',
				'url' => 'jobfair/index/jobfair_show?id=:1'
			),
			'QS_jobfairexhibitors' => array (
				'rewrite' => 'jobfair/jobfair-exhibitors-($id)-($page)',
				'url_reg' => '/^jobfair\/jobfair\-exhibitors\-(.*)\-(.*)$/',
				'url' => 'jobfair/index/jobfair_com?id=:1&p=:2'
			),
			'QS_jobfair_booth' => array (
				'rewrite' => 'jobfair/reserve-($id)',
				'url_reg' => '/^jobfair\/reserve\-(.*)$/',
				'url' => 'jobfair/index/jobfair_reserve?id=:1'
			),
			'QS_jobfair_traffic' => array (
				'rewrite' => 'jobfair/traffic-($id)',
				'url_reg' => '/^jobfair\/traffic\-(.*)$/',
				'url' => 'jobfair/index/jobfair_traffic?id=:1'
			),
			'QS_jobfair_retrospect' => array (
				'rewrite' => 'jobfair/retrospect-($id)',
				'url_reg' => '/^jobfair\/retrospect\-(.*)$/',
				'url' => 'jobfair/index/jobfair_retrospect?id=:1'
			),
			'QS_map' => array (
				'rewrite' => 'jobs/map-search-($id)',
				'url_reg' => '/^jobs\/map\-search\-(.*)$/',
				'url' => 'home/jobs/jobs_map'
			),
			'QS_help' => array (
				'rewrite' => 'help/',
				'url_reg' => '/^help$/',
				'url' => 'home/help/index'
			),
			'QS_helplist' => array (
				'rewrite' => 'help/help-list-($id)-($page)',
				'url_reg' => '/^help\/help\-list\-(.*)\-(.*)$/',
				'url' => 'home/help/help_list?id=:1&p=:2'
			),
			'QS_helpshow' => array (
				'rewrite' => 'help/help-show-($id)',
				'url_reg' => '/^help\/help\-show\-(.*)$/',
				'url' => 'home/help/help_show?id=:1'
			),
			'QS_suggest' => array (
				'rewrite' => 'suggest/index',
				'url_reg' => '/^suggest\/index$/',
				'url' => 'home/suggest/index'
			),
			'QS_mall_index' => array (
				'rewrite' => 'shop/index',
				'url_reg' => '/^shop\/index$/',
				'url' => 'mall/index/index'
			),
			'QS_goods_list' => array (
				'rewrite' => 'shop/shop_list',
				'url_reg' => '/^shop\/shop_list$/',
				'url' => 'mall/index/goods_list'
			),
			'QS_goods_show' => array (
				'rewrite' => 'shop/shop_show-($id)',
				'url_reg' => '/^shop\/shop\_show\-(.*)$/',
				'url' => 'mall/index/goods_show?id=:1'
			),
			'QS_mall_charts' => array (
				'rewrite' => 'shop/shop_charts_list',
				'url_reg' => '/^shop\/shop\_charts\_list$/',
				'url' => 'mall/index/charts'
			),
            'QS_company_report' => array (
                'rewrite' => 'report/report-show-($id)',
                'url_reg' => '/^report\/report\-show\-(.*)$/',
                'url' => 'report/index/index?id=:1'
            ),
            'QS_seniorjobfairlist' => array (
				'rewrite' => 'seniorjobfair/jobfair-list-($page)',
				'url_reg' => '/^seniorjobfair\/jobfair\-list\-(.*)$/',
				'url' => 'seniorjobfair/index/index?p=:1'
			),
			'QS_seniorjobfairshow' => array (
				'rewrite' => 'seniorjobfair/jobfair-show-($id)',
				'url_reg' => '/^seniorjobfair\/jobfair\-show\-(.*)$/',
				'url' => 'seniorjobfair/index/jobfair_show?id=:1'
			),
			'QS_seniorjobfairexhibitors' => array (
				'rewrite' => 'seniorjobfair/jobfair-exhibitors-($id)-($page)',
				'url_reg' => '/^seniorjobfair\/jobfair\-exhibitors\-(.*)\-(.*)$/',
				'url' => 'seniorjobfair/index/jobfair_com?id=:1&p=:2'
			),
			'QS_seniorjobfair_booth' => array (
				'rewrite' => 'seniorjobfair/reserve-($id)',
				'url_reg' => '/^seniorjobfair\/reserve\-(.*)$/',
				'url' => 'seniorjobfair/index/jobfair_reserve?id=:1'
			),
			'QS_seniorjobfair_traffic' => array (
				'rewrite' => 'seniorjobfair/traffic-($id)',
				'url_reg' => '/^seniorjobfair\/traffic\-(.*)$/',
				'url' => 'seniorjobfair/index/jobfair_traffic?id=:1'
			),
			'QS_seniorjobfair_retrospect' => array (
				'rewrite' => 'seniorjobfair/retrospect-($id)',
				'url_reg' => '/^seniorjobfair\/retrospect\-(.*)$/',
				'url' => 'seniorjobfair/index/jobfair_retrospect?id=:1'
			),
			'QS_interview_list' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => ''
			),
			'QS_interview_show' => array (
				'rewrite' => 'interview/interview-show-($id)',
				'url_reg' => '/^interview\/interview\-show\-(\d+)$/',
				'url' => 'interview/index/interview_show?id=:1'
			),
			'QS_career_list' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => ''
			),
			'QS_career_show' => array (
				'rewrite' => 'career/career-show-($id)',
				'url_reg' => '/^career\/career\-show\-(\d+)$/',
				'url' => 'career/index/career_show?id=:1'
			),
			'QS_subject_list' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => ''
			),
			'QS_subject_show' => array (
				'rewrite' => 'subject/subject-show-($id)',
				'url_reg' => '/^subject\/subject\-show\-(\d+)$/',
				'url' => 'subject/index/subject_show?id=:1'
			),
			'QS_beautyhr_show' => array (
				'rewrite' => 'beautyhr/beautyhr-show-($id)',
				'url_reg' => '/^beautyhr\/index\-show\-(\d+)$/',
				'url' => 'beautyhr/index/show?id=:1'
			),
			'QS_subject_p_show' => array (
				'rewrite' => 'subject/subject_p_show-($id)',
				'url_reg' => '/^subject\/subject\_p\_show\-(\d+)$/',
				'url' => 'subject/index/subject_p_show?id=:1'
			),	
		)
	);
?>