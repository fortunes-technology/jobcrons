<?php

    putenv('AWS_ACCESS_KEY_ID=AKIAQLRVICZQM2MQJHZM');
    putenv('AWS_SECRET_ACCESS_KEY=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');   

    $output = shell_exec('/usr/local/bin/aws sts assume-role --role-arn arn:aws:iam::586658450857:role/tmn-external-access-bebee --role-session-name sessionname --external-id indeed-bebee');
    
    $output = json_decode($output);
    
    if(isset($output->Credentials)) {
        $accessKeyId = $output->Credentials->AccessKeyId;
        $secretAccessKey = $output->Credentials->SecretAccessKey;
        $sessionToken = $output->Credentials->SessionToken;
        putenv("AWS_ACCESS_KEY_ID=".$accessKeyId);
        putenv("AWS_SECRET_ACCESS_KEY=".$secretAccessKey);
        putenv("AWS_SESSION_TOKEN=".$sessionToken);  
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/61A33D1B-8F59-43C1-87B3-9003664CB289.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/405134F8-9B1A-4096-B2D0-FF90B19520E6.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/DD8C4CCC-CDB3-41BC-BE19-42C5501A8F62.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/5D1CA841-7874-4A81-8792-076983FFF98A.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/17A9AFE4-480F-4CA4-AD1F-373CF2CCADC6.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/0598BB6B-9561-4492-B673-65C94B9ACEF6.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/537363C4-1C8A-49BD-9F53-4D9BB957B339.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/C95DD8FD-D49F-471D-AE56-0CE9C604BAA4.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/BDA84857-9CE1-40CE-AB33-6C82B8AD8D3B.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/0DE55FE9-6E92-48EC-BEF4-B212A74C5731.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/06C705BB-F7DC-4D44-82A6-46D2BDAFF25B.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/225D6D02-5B94-4358-8E2E-65B6425E60B4.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/2FBFDBD2-8E0E-4ABA-A7EC-8CB0CDB1D6FD.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/D6B0B4E3-6CED-4513-95A4-4A0CC8A5CF26.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/23E0836E-9CFA-48F4-ADC0-99049A9384F6.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/2859044C-9D7E-40AC-9D81-61B1221D2D3C.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/D7FAD52E-C2DC-4DC3-B871-5FF850C3EE5C.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/3C6F8096-ED41-4EBB-A9A0-431D9E5FECD2.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/88598975-59B7-437D-A31D-1EC5D71677AA.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/B8CAF50B-DA6E-416C-9484-6E868BC17471.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/31114991-D0E0-4663-8678-F98E08C92A47.xml /var/www/html/cf/');
        $output = shell_exec('/usr/local/bin/aws s3 cp s3://tmn-export/bebee/FA978A8B-9EE2-4F1E-81EC-8032AAF87F34.xml /var/www/html/cf/');
    }

?>