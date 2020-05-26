<?php
	return array(
		'alias' => 'min',
		'name' => '极简',
		'explain' => '说明',
		'versions' => '1.0.0',
		'update_time' => '2016-09-09',
		'author' => '74cms',
		'suffix' => '.html',
		'config_url' => array(
			'QS_index' => array (
				'rewrite' => '',
				'url_reg' => '/^index$/',
				'url' => 'home/index/index'
			),
			'QS_jobs' => array (
				'rewrite' => 'jobs',
				'url_reg' => '/^jobs$/',
				'url' => 'home/jobs/index'
			),
			'QS_jobslist' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => '',
			),
			'QS_companylist' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => '',
			),
			'QS_jobsshow' => array (
				'rewrite' => 'jobs/($id)',
				'url_reg' => '/^jobs\/(\d+)$/',
				'url' => 'home/jobs/jobs_show?id=:1'
			),
			'QS_companyshow' => array (
				'rewrite' => 'company/($id)',
				'url_reg' => '/^company\/(\d+)$/',
				'url' => 'home/jobs/com_show?id=:1'
			),
			'QS_companyjobs' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => ''
			),
			'QS_login' => array (
				'rewrite' => 'members/login',
				'url_reg' => '/^members\/login$/',
				'url' => 'home/members/login'
			),
			'QS_resume' => array (
				'rewrite' => 'resume',
				'url_reg' => '/^resume$/',
				'url' => 'home/resume/index'
			),
			'QS_resumelist' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => ''
			),
			'QS_resumeshow' => array (
				'rewrite' => 'resume/($id)',
				'url_reg' => '/^resume\/(\d+)$/',
				'url' => 'home/resume/resume_show?id=:1'
			),
			'QS_hrtools' => array (
				'rewrite' => 'hrtools',
				'url_reg' => '/^hrtools$/',
				'url' => 'home/hrtools/index'
			),
			'QS_hrtoolslist' => array (
				'rewrite' => 'hrtools/list/($id)',
				'url_reg' => '/^hrtools\/list\/(.*)$/',
				'url' => 'home/hrtools/hrtools_list?id=:1'
			),
			'QS_news' => array (
				'rewrite' => 'news',
				'url_reg' => '/^news$/',
				'url' => 'home/news/index'
			),
			'QS_newslist' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => ''
			),
			'QS_newsshow' => array (
				'rewrite' => 'news/($id)',
				'url_reg' => '/^news\/(\d+)$/',
				'url' => 'home/news/news_show?id=:1'
			),
			'QS_explainshow' => array (
				'rewrite' => 'explain/($id)',
				'url_reg' => '/^explain\/(.*)$/',
				'url' => 'home/explain/explain_show?id=:1'
			),
			'QS_noticelist' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => ''
			),
			'QS_noticeshow' => array (
				'rewrite' => 'notice/($id)',
				'url_reg' => '/^notice\/(\d+)$/',
				'url' => 'home/notice/notice_show?id=:1'
			),
			'QS_jobfairlist' => array (
				'rewrite' => 'jobfair/list-($page)',
				'url_reg' => '/^jobfair\/list\-(.*)$/',
				'url' => 'jobfair/index/index?p=:1'
			),
			'QS_jobfairshow' => array (
				'rewrite' => 'jobfair/($id)',
				'url_reg' => '/^jobfair\/(\d+)$/',
				'url' => 'jobfair/index/jobfair_show?id=:1'
			),
			'QS_jobfairexhibitors' => array (
				'rewrite' => 'jobfair/com-($id)-($page)',
				'url_reg' => '/^jobfair\/com\-(.*)\-(.*)$/',
				'url' => 'jobfair/index/jobfair_com?id=:1&p=:2'
			),
			'QS_jobfair_booth' => array (
				'rewrite' => 'jobfair/reserve/($id)',
				'url_reg' => '/^jobfair\/reserve\/(.*)$/',
				'url' => 'jobfair/index/jobfair_reserve?id=:1'
			),
			'QS_jobfair_traffic' => array (
				'rewrite' => 'jobfair/traffic/($id)',
				'url_reg' => '/^jobfair\/traffic\/(.*)$/',
				'url' => 'jobfair/index/jobfair_traffic?id=:1'
			),
			'QS_jobfair_retrospect' => array (
				'rewrite' => 'jobfair/retrospect/($id)',
				'url_reg' => '/^jobfair\/retrospect\/(.*)$/',
				'url' => 'jobfair/index/jobfair_retrospect?id=:1'
			),
			'QS_map' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => ''
			),
			'QS_help' => array (
				'rewrite' => 'help',
				'url_reg' => '/^help$/',
				'url' => 'home/help/index'
			),
			'QS_helplist' => array (
				'rewrite' => 'help/list-($id)-($page)',
				'url_reg' => '/^help\/list\-(.*)\-(.*)$/',
				'url' => 'home/help/help_list?id=:1&p=:2'
			),
			'QS_helpshow' => array (
				'rewrite' => 'help/($id)',
				'url_reg' => '/^help\/(.*)$/',
				'url' => 'home/help/help_show?id=:1'
			),
			'QS_suggest' => array (
				'rewrite' => 'suggest',
				'url_reg' => '/^suggest$/',
				'url' => 'home/suggest/index'
			),
			'QS_mall_index' => array (
				'rewrite' => 'mall',
				'url_reg' => '/^mall$/',
				'url' => 'mall/index/index'
			),
			'QS_goods_list' => array (
				'rewrite' => 'mall/list',
				'url_reg' => '/^mall\/list$/',
				'url' => 'mall/index/goods_list'
			),
			'QS_goods_show' => array (
				'rewrite' => 'mall/goods/($id)',
				'url_reg' => '/^mall\/goods\/(.*)$/',
				'url' => 'mall/index/goods_show?id=:1'
			),
			'QS_mall_charts' => array (
				'rewrite' => 'mall/charts',
				'url_reg' => '/^mall\/charts$/',
				'url' => 'mall/index/charts'
			),
            'QS_company_report' => array (
                'rewrite' => 'report/($id)',
                'url_reg' => '/^report\/(\d+)$/',
                'url' => 'report/index/index?id=:1'
            ),
            'QS_seniorjobfairlist' => array (
				'rewrite' => 'seniorjobfair/list-($page)',
				'url_reg' => '/^seniorjobfair\/list\-(.*)$/',
				'url' => 'seniorjobfair/index/index?p=:1'
			),
			'QS_seniorjobfairshow' => array (
				'rewrite' => 'seniorjobfair/($id)',
				'url_reg' => '/^seniorjobfair\/(\d+)$/',
				'url' => 'seniorjobfair/index/jobfair_show?id=:1'
			),
			'QS_seniorjobfairexhibitors' => array (
				'rewrite' => 'seniorjobfair/com-($id)-($page)',
				'url_reg' => '/^seniorjobfair\/com\-(.*)\-(.*)$/',
				'url' => 'seniorjobfair/index/jobfair_com?id=:1&p=:2'
			),
			'QS_seniorjobfair_booth' => array (
				'rewrite' => 'seniorjobfair/reserve/($id)',
				'url_reg' => '/^seniorjobfair\/reserve\/(.*)$/',
				'url' => 'seniorjobfair/index/jobfair_reserve?id=:1'
			),
			'QS_seniorjobfair_traffic' => array (
				'rewrite' => 'seniorjobfair/traffic/($id)',
				'url_reg' => '/^seniorjobfair\/traffic\/(.*)$/',
				'url' => 'seniorjobfair/index/jobfair_traffic?id=:1'
			),
			'QS_seniorjobfair_retrospect' => array (
				'rewrite' => 'seniorjobfair/retrospect/($id)',
				'url_reg' => '/^seniorjobfair\/retrospect\/(.*)$/',
				'url' => 'seniorjobfair/index/jobfair_retrospect?id=:1'
			),
			'QS_interview_list' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => ''
			),
			'QS_interview_show' => array (
				'rewrite' => 'interview/($id)',
				'url_reg' => '/^interview\/(\d+)$/',
				'url' => 'interview/index/interview_show?id=:1'
			),
			'QS_career_list' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => ''
			),
			'QS_career_show' => array (
				'rewrite' => 'career/($id)',
				'url_reg' => '/^career\/(\d+)$/',
				'url' => 'career/index/career_show?id=:1'
			),
			'QS_subject_list' => array (
				'rewrite' => '',
				'url_reg' => '',
				'url' => ''
			),
			'QS_subject_show' => array (
				'rewrite' => 'subject/($id)',
				'url_reg' => '/^subject\/(\d+)$/',
				'url' => 'subject/index/subject_show?id=:1'
			),
			'QS_subject_p_show' => array (
				'rewrite' => 'subject/($id)',
				'url_reg' => '/^subject\/(\d+)$/',
				'url' => 'subject/index/subject_p_show?id=:1'
			)
		)
	);
?>