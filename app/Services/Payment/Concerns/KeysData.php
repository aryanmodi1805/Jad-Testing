<?php

namespace App\Services\Payment\Concerns;

enum KeysData : string
{

    const Key = 'Key';
    const PublicKey= 'PublicKey';
    const UserName = 'UserName';
    const Password = 'Password';
    const Hmac = 'Hmac';
    const RequestId = 'RequestId';
    const CertificatePath = 'CertificatePath';
    const CertificatePassword = 'CertificatePassword';
    const Port = 'Port';

    const Category = 'Category';
    const AccountID = 'AccountID';
    const PosID = 'PosID';
    const BankID = 'BankID';

    const AppId = 'AppId';

    const BranchName = 'BranchName';
    const ReceiverName = 'ReceiverName';
    const ReceiverMobile = 'ReceiverMobile';
    const PublishableKey = 'PublishableKey';
    const SecretKey = 'SecretKey';
    const WebhookKey = 'WebhookKey';
    const  MerchantID = 'MerchantID';
    const  ServerKey = 'ServerKey';
    const  ClientKey = 'ClientKey';
    const  MethodKey = 'MethodKey';

    const SourceID = 'SourceID';

}
