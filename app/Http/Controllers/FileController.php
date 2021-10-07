<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /** Api url */
    const API_URL_UPLOAD_FILE_S3 = 'file/upload-to-s3';

    /** Method */
    const METHOD_UPLOAD_FILE_S3 = 'uploadFileS3';

    // Error code
    const CODE_INVALID_OWNER_TYPE = 'ERR400017';
    const CODE_SAVE_FILE_TO_DB_FAIL = 'ERR400018';
    const CODE_NOT_FOUND_MODEL = 'ERR400019';
    const CODE_NOT_FOUND_FILE = 'ERR400020';
    const CODE_INTERNAL_ERROR_WHEN_SAVING_FILE = 'EX500009';
    const CODE_INTERNAL_ERROR_WHEN_GETTING_PRESIGN_URL = 'EX500010';
    const CODE_ERROR_WHEN_UPLOADING_FILE = 'ERR400021';
    const CODE_NOT_FOUND_FILE_OF_OWNER = 'ERR400022';
    const CODE_NO_RIGHT_FOR_CHANGE_FILE = 'ERR400023';

    // Error message
    const MESSAGE_INVALID_OWNER_TYPE = 'Invalid owner type of file.';
    const MESSAGE_SAVE_FILE_TO_DB_FAIL = 'Save file to DB failed.';
    const MESSAGE_NOT_FOUND_MODEL = 'Not found any model with this id.';
    const MESSAGE_NOT_FOUND_FILE = 'Message not found file to save.';
    const MESSAGE_ERROR_WHEN_UPLOADING_FILE = 'Upload file failed.';
    const MESSAGE_NOT_FOUND_FILE_OF_OWNER = 'Not found file of this owner.';
    const MESSAGE_NO_RIGHT_FOR_CHANGE_FILE = 'You have no right to change file not belong to you.';

    // Successful code
    const CODE_GET_PRESIGN_URL_SUCCESS = 'ST200009';
    const CODE_UPLOAD_FILE_SUCCESS = 'ST200010';

    // Successful message
    const MESSAGE_GET_PRESIGN_URL_SUCCESS = 'Get pre-signed url successfully.';
    const MESSAGE_UPLOAD_FILE_SUCCESS = 'Upload file successfully.';

    /** Send file option for relations */
    const OPTION_1_1 = '1-1';

    /**
     * @functionName: saveFile
     * @type:         public
     * @param:        somes
     * @return:       object|array
     */
    public function saveFileToDB($path, $type, $ownerTypeModel, $ownerId, $fileId = null, $option = self::OPTION_1_1)
    {
        try {
            if (!$fileId or empty($fileId)) {
                if ($option == self::OPTION_1_1) {
                    $dataForUpdate = File::where(File::COL_OWNER_TYPE, $ownerTypeModel)
                        ->where(File::COL_OWNER_ID, $ownerId)->get();
                    if (!$dataForUpdate->isEmpty()) {
                        if (count($dataForUpdate) == 1) {
                            $dataForUpdate = $dataForUpdate->first();
                            $dataForUpdate->{File::COL_PATH} = $path;
                            if (!$dataForUpdate->save()) {
                                return [
                                    self::KEY_CODE => 400,
                                    self::KEY_DETAIL_CODE => self::CODE_SAVE_FILE_TO_DB_FAIL,
                                    self::KEY_MESSAGE => self::MESSAGE_SAVE_FILE_TO_DB_FAIL,
                                ];
                            }
                            return $dataForUpdate;
                        } else {
                            //...
                        }
                    }
                }
                $dataForCreate = [
                    File::COL_PATH => $path,
                    File::COL_OWNER_ID => $ownerId,
                    File::COL_OWNER_TYPE => $ownerTypeModel,
                    File::COL_TYPE => $type,
                ];
                $savedModel = File::create($dataForCreate);
            } else { //update
                $file = File::find($fileId);
                if (!$file) {
                    return [
                        self::KEY_CODE => 400,
                        self::KEY_DETAIL_CODE => self::CODE_NOT_FOUND_FILE,
                        self::KEY_MESSAGE => self::MESSAGE_NOT_FOUND_FILE,
                    ];
                }
                $file->{File::COL_PATH} = $path;
                $file->{File::COL_TYPE} = $type;
                $file->{File::COL_OWNER_ID} = $ownerId;
                $file->{File::COL_OWNER_TYPE} = $ownerTypeModel;

                $savedModel = $file->save();
            }
            if (!$savedModel) {
                return [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_SAVE_FILE_TO_DB_FAIL,
                    self::KEY_MESSAGE => self::MESSAGE_SAVE_FILE_TO_DB_FAIL,
                ];
            }

            return $savedModel;
        } catch (Exception $ex) {
            return [
                self::KEY_CODE => 500,
                self::KEY_DETAIL_CODE => self::CODE_INTERNAL_ERROR_WHEN_SAVING_FILE,
                self::KEY_MESSAGE => $ex->getMessage(),
            ];
        }
    }

    /**
     * @functionName: getPresignedUrl
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     * NOT USE - tham khảo.
     */
    public function getPresignedUrl(Request $request)
    {
        try {
            $client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();
            $fileName = \Str::random(10) . '_' . $request->file_name;
            $filePath = 'images/' . $fileName;

            $command = $client->getCommand('PutObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $filePath,
            ]);

            $request = $client->createPresignedRequest($command, '+20 minutes');

            $responseData = [
                'file_path' => $filePath,
                'pre_signed' => (string) $request->getUri(),
            ];

            $response = [
                self::KEY_CODE => 200,
                self::KEY_DETAIL_CODE => self::CODE_GET_PRESIGN_URL_SUCCESS,
                self::KEY_DATA => $responseData,
                self::KEY_MESSAGE => self::MESSAGE_GET_PRESIGN_URL_SUCCESS,
            ];
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = [
                self::KEY_CODE => 500,
                self::KEY_DETAIL_CODE => self::CODE_INTERNAL_ERROR_WHEN_GETTING_PRESIGN_URL,
                self::KEY_MESSAGE => $ex->getMessage(),
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * @functionName: uploadFileS3
     * @type:         public
     * @param:        Request $request
     * @return:       String(Json)
     */
    public function uploadFileS3(Request $request)
    {
        $fileId = $request->{File::VAL_FILE_ID};
        $type = $request->{File::COL_TYPE};
        $ownerType = $request->{File::VAL_OWNER_TYPE};
        $ownerId = $request->{File::VAL_OWNER_ID};
        $index = $request->{File::VAL_INDEX};

        $file = $request->{File::VAL_FILE};

        try {
            $validator = File::validator([
                File::VAL_INDEX => $index,
                File::COL_TYPE => $type,
                File::VAL_OWNER_TYPE => $ownerType,
                File::VAL_OWNER_ID => $ownerId,
            ]);
            if ($validator->fails()) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_INVALID_FIELD,
                    self::KEY_MESSAGE => $validator->errors()->first(),
                ];
                return response()->json($response, 400);
            }

            $request->validate([File::VAL_FILE => File::FILE_VALIDATIONS[$type]]);

            $ownerTypeModel = File::OWNER_TYPE_MODELS[$ownerType] ?? false;
            if (!$ownerTypeModel) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_INVALID_OWNER_TYPE,
                    self::KEY_MESSAGE => self::MESSAGE_INVALID_OWNER_TYPE,
                ];
                return response()->json($response, 400);
            }
            $ownerModel = $ownerTypeModel::find($ownerId);
            if (!$ownerModel) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_NOT_FOUND_MODEL,
                    self::KEY_MESSAGE => self::MESSAGE_NOT_FOUND_MODEL,
                ];
                return response()->json($response, 400);
            }
            $checkOwnerFile = $this->checkOwnerOfFile($ownerType, $ownerId);
            $haveRightChange = $this->checkRight($ownerType);
            if (!$checkOwnerFile or !$haveRightChange) {
                $response = [
                    self::KEY_CODE => 400,
                    self::KEY_DETAIL_CODE => self::CODE_NO_RIGHT_FOR_CHANGE_FILE,
                    self::KEY_MESSAGE => self::MESSAGE_NO_RIGHT_FOR_CHANGE_FILE,
                ];
                return response()->json($response, 400);
            }

            $fileFolder = $ownerType.'s/';
            $fileName = $ownerType.'_'.$ownerId;
            if ($index) {
                $fileName = $fileName.'_'.$index;
            }
            $fullPath = $this->uploadFile($file, $fileName, $fileFolder);

            $rsSaveToDB = $this->saveFileToDB($fullPath, $type, $ownerTypeModel, $ownerId, $fileId);
            if (gettype($rsSaveToDB) == 'array') {
                return response()->json($rsSaveToDB, $rsSaveToDB[self::KEY_CODE]);
            }
            $fileId = $rsSaveToDB->{File::COL_ID};
            $dataResponse = [
                'path' => $fullPath,
                'fileId' => $fileId,
            ];

            $response = [
                self::KEY_CODE => 200,
                self::KEY_DETAIL_CODE => self::CODE_UPLOAD_FILE_SUCCESS,
                self::KEY_DATA => $dataResponse,
                self::KEY_MESSAGE => self::MESSAGE_UPLOAD_FILE_SUCCESS,
            ];
            return response()->json($response, 200);
        } catch (Exception $ex) {
            $response = [
                self::KEY_CODE => 500,
                self::KEY_DETAIL_CODE => self::CODE_ERROR_WHEN_UPLOADING_FILE,
                self::KEY_MESSAGE => $ex->getMessage() . ' - Please check type and size(max: 2MB) of file',
            ];
            return response()->json($response, 500);
        }
    }

    private function uploadFile($file, $fileName, $fileFolder = 'common/')
    {
        $filePath = $fileFolder . $fileName;
        $file->storeAs($fileFolder, $fileName, 's3');
        return Storage::disk('s3')->url($filePath);
    }

    private function deleteFile($filePath)
    {
        Storage::disk('s3')->delete($filePath);
    }

    private function checkOwnerOfFile($ownerType, $ownerId)
    {
        if (User::class == File::OWNER_TYPE_MODELS[$ownerType]) {
            $curUserId = Auth::user()->{User::COL_ID};
            if ($curUserId != $ownerId) {
                return false;
            }
        }
        return true;
    }

    private function checkRight($ownerType)
    {
        $currentUser = Auth::user();
        $right = File::OWNER_TYPE_RIGHT[$currentUser->{User::COL_ROLE_ID}][$ownerType] ?? false;
        return $right;
    }
}
