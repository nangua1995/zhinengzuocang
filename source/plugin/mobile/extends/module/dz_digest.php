<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: dz_digest.php 33590 2013-07-12 06:39:08Z andyzheng $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class dz_digest extends extends_data {
	function __construct() {
		parent::__construct();
	}

	public static function common() {
		global $_G;
		self::$page = intval($_GET['page']) ? intval($_GET['page']) : 1;
		$start = (self::$page - 1)*self::$perpage;
		$num = self::$perpage;
		loadcache('forum_guide');
		$dateline = 0;
		$maxnum = 50000;
		$_G['setting']['guide'] = dunserialize($_G['setting']['guide']);
		if($_G['setting']['guide']['digestdt']) {
			$dateline = time() - intval($_G['setting']['guide']['digestdt']);
		}
		$maxtid = C::t('forum_thread')->fetch_max_tid();
		$limittid = max(0,($maxtid - $maxnum));
		$tids = array_slice($_G['cache']['forum_guide']['digest']['data'], $start ,$num);
		$query = C::t('forum_thread')->fetch_all_for_guide('digest', $limittid, $tids, $_G['setting']['heatthread']['guidelimit'], $dateline);

		$fids = array();
		loadcache('forums');
		foreach($_G['cache']['forums'] as $fid => $forum) {
			if($forum['type'] != 'group' && $forum['status'] > 0 && (!$forum['viewperm'] && $_G['group']['readaccess']) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
				$fids[] = $fid;
			}
		}
		$list = array();
		$n = 0;
		foreach($query as $thread) {
			if(empty($tids) && ($thread['isgroup'] || !in_array($thread['fid'], $fids))) {
				continue;
			}
			if($thread['displayorder'] < 0) {
				continue;
			}
			if($tids || ($n >= $start && $n < ($start + $num))) {
				$list[$thread['tid']] = $thread;
			}
			$n ++;
		}
		$threadlist = array();
		if($tids) {
			foreach($tids as $key => $tid) {
				if($list[$tid]) {
					$threadlist[$key] = $list[$tid];
				}
			}
		} else {
			$threadlist = $list;
		}
		unset($list);

		foreach($threadlist as $thread) {
			self::field('author', '0', $thread['author']);
			self::field('dateline', '0', $thread['dateline']);
			self::field('replies', '1', $thread['replies']);
			self::field('views', '2', $thread['views']);
			self::$id = $thread['tid'];
			self::$title = $thread['subject'];
			self::$image = '';
			self::$icon = '1';
			self::$poptype = '0';
			self::$popvalue = '';
			self::$clicktype = 'tid';
			self::$clickvalue = $thread['tid'];

			self::insertrow();
		}
	}
}
?>