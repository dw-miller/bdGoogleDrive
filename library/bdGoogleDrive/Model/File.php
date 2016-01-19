<?php

class bdGoogleDrive_Model_File extends XenForo_Model
{
    public function isIgnored(array $attachmentData)
    {
        return false;
    }

    public function saveFilePath($path, $fileName, $fileHash, $accessToken)
    {
        return $this->saveFileData(file_get_contents($path), $fileName, $fileHash, $accessToken);
    }

    public function saveFileData($data, $fileName, $fileHash, $accessToken)
    {
        $createdFile = bdGoogleDrive_Helper_Api::uploadFile($accessToken, $fileName, $data, array(
            'description' => $fileHash,
            'parentId' => bdGoogleDrive_Option::getDefaultFolderId(),
        ));

        if (!empty($createdFile['id'])) {
            // https://code.google.com/a/google.com/p/apps-api-issues/issues/detail?id=3717
            // make another request to set the file public
            bdGoogleDrive_Helper_Api::makeFilePublic($accessToken, $createdFile['id']);
        }

        return $createdFile;
    }

    public function deleteFile($data)
    {

    }

    public function getFileUrl(array $file)
    {
        return $file['link'];
    }

    public function getTemporaryFileUrl(array $file)
    {
        $fileUrl = $this->getFileUrl($file);

        $ch = curl_init($fileUrl);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers = curl_exec($ch);

        if (preg_match('#Location: (?<url>http.+)\s#', $headers, $matches)) {
            return $matches['url'];
        } else {
            return $fileUrl;
        }
    }
}