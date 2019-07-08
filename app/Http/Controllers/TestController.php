<?php
/**
 * Created by PhpStorm.
 * User: brucewei
 * Date: 2019/3/5
 * Time: 4:39 PM
 */

namespace App\Http\Controllers;


class TestController extends Controller {
	public function test() {
//		$date = '2019-04-14 14:44:07';
//		$m    = date( 'm-d', strtotime( $date ) );
//		$re   = $this->get_constellation( strtotime( $date ) );
		$d = '{大学}';

		return ltrim( rtrim( $d, '}' ), '{' );
	}

	function get_constellation( $time ) {
		$y           = date( "Y" ) . '-';
		$his         = ' 00:00:00';
		$birth_month = date( "m", $time );
		$birth_date  = date( "d", $time );

		$userTime = strtotime( $y . $birth_month . '-' . $birth_date . $his );

		$januaryS   = strtotime( $y . '01-20' . $his );
		$januaryE   = strtotime( $y . '02-18' . $his );
		$februaryS  = strtotime( $y . '02-19' . $his );
		$februaryE  = strtotime( $y . '03-20' . $his );
		$marchS     = strtotime( $y . '03-21' . $his );
		$marchE     = strtotime( $y . '04-19' . $his );
		$aprilS     = strtotime( $y . '04-20' . $his );
		$aprilE     = strtotime( $y . '05-20' . $his );
		$mayS       = strtotime( $y . '05-21' . $his );
		$mayE       = strtotime( $y . '06-21' . $his );
		$juneS      = strtotime( $y . '06-22' . $his );
		$juneE      = strtotime( $y . '07-22' . $his );
		$julyS      = strtotime( $y . '07-23' . $his );
		$julyE      = strtotime( $y . '08-22' . $his );
		$augustS    = strtotime( $y . '08-23' . $his );
		$augustE    = strtotime( $y . '09-22' . $his );
		$septemberS = strtotime( $y . '09-23' . $his );
		$septemberE = strtotime( $y . '10-23' . $his );
		$octoberS   = strtotime( $y . '10-24' . $his );
		$octoberE   = strtotime( $y . '11-22' . $his );
		$novemberS  = strtotime( $y . '11-23' . $his );
		$novemberE  = strtotime( $y . '12-21' . $his );

		if ( $userTime >= $januaryS && $userTime <= $januaryE ) {
			$constellation = '水瓶座';
		} elseif ( $userTime >= $februaryS && $userTime <= $februaryE ) {
			$constellation = '双鱼座';
		} elseif ( $userTime >= $marchS && $userTime <= $marchE ) {
			$constellation = '白羊座';
		} elseif ( $userTime >= $aprilS && $userTime <= $aprilE ) {
			$constellation = '金牛座';
		} elseif ( $userTime >= $mayS && $userTime <= $mayE ) {
			$constellation = '双子座';
		} elseif ( $userTime >= $juneS && $userTime <= $juneE ) {
			$constellation = '巨蟹座';
		} elseif ( $userTime >= $julyS && $userTime <= $julyE ) {
			$constellation = '狮子座';
		} elseif ( $userTime >= $augustS && $userTime <= $augustE ) {
			$constellation = '处女座';
		} elseif ( $userTime >= $septemberS && $userTime <= $septemberE ) {
			$constellation = '天秤座';
		} elseif ( $userTime >= $octoberS && $userTime <= $octoberE ) {
			$constellation = '天蝎座';
		} elseif ( $userTime >= $novemberS && $userTime <= $novemberE ) {
			$constellation = '射手座';
		} else {
			$constellation = '摩羯座';
		}

		return $constellation;
	}
}