<?php

namespace Zimutech;

class IdCard
{
	const BASE_URL = 'https://idenauthen.market.alicloudapi.com/idenAuthentication';

	protected $appCode;
	
	function __construct(string $appCode)
	{
		$this->appCode = $appCode;
	}
	
	public function verify(string $realname, string $idnum) : bool
	{
		$data['name'] = $realname;
		$data['idNo'] = $idnum;

		if($this->checksum($idnum) === false) {
			return false;
		}

		$result = $this->httpRequest($data);

		if($result['respCode'] === '0000') {
			return true;
		} else {
			return false;
		}
	}

	public function getData(string $idnum) : array
	{
		$data['region_id'] = substr($idnum, 0, 6);
		$data['birthday'] = substr($idnum, 6, 4) . '-' . substr($idnum, 10, 2) . '-' . substr($idnum, 12, 2);
		$data['gender'] = $idnum[16] % 2 === 0 ? 2 : 1;
		
		return $data;
	}

	protected function checksum(string $idnum) : bool
	{
		$multiple = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $crc = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        $sum = 0;
        
        for($i = 0; $i < 17; $i++)
        {
            $sum += $idnum[$i] * $multiple[$i];
        }
        
        $m = $sum % 11;
                
        return $idnum[17] === $crc[$m];
	}

	protected function httpRequest(array $data)
	{
		$ch = curl_init(self::BASE_URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Authorization: APPCODE " . $this->appCode,
			"Content-Type: application/x-www-form-urlencoded"
		]);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		$output = curl_exec($ch);
		curl_close($ch);

		return json_decode($output, true);
	}
}