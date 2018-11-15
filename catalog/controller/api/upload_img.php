<?php
class ControllerApiUploadImg extends Controller{
    public function index()
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->load->language('tool/upload');

        $allowKey       = ['api_token'];
        $req_data       = $this->dataFilter($allowKey);
        $data           = $this->returnData();
        $json           = [];

        if (!$this->checkSign($req_data)) {
            return $this->response->setOutput($this->returnData(['msg'=>'fail:sign error']));
        }

        if (!(isset($req_data['tag']) &&  !empty($req_data['tag']))) {
            return $this->response->setOutput($this->returnData(['code'=>'203','msg'=>'fail:token is error']));
        }

        $dirname        = $req_data['tag'] . '/';

        if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
            // Sanitize the filename
            $filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));

            // Validate the filename length
            if ((utf8_strlen($filename) < 2) || (utf8_strlen($filename) > 64)) {
                return $this->response->setOutput($this->returnData(['msg'=>$this->language->get('error_filename')]));
            }

            // Allowed file extension types
            $allowed           = [];
            $extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));
            $filetypes         = explode("\n", $extension_allowed);

            foreach ($filetypes as $filetype) {
                $allowed[] = trim($filetype);
            }

            if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
                return $this->response->setOutput($this->returnData(['msg'=>$this->language->get('error_filename')]));
            }

            // Allowed file mime types
            $allowed           = [];
            $mime_allowed      = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));
            $filetypes         = explode("\n", $mime_allowed);

            foreach ($filetypes as $filetype) {
                $allowed[]     = trim($filetype);
            }

            if (!in_array($this->request->files['file']['type'], $allowed)) {
                return $this->response->setOutput($this->returnData(['msg'=>$this->language->get('error_filetype')]));
            }

            // Check to see if any PHP files are trying to be uploaded
            $content           = file_get_contents($this->request->files['file']['tmp_name']);

            if (preg_match('/\<\?php/i', $content)) {
                return $this->response->setOutput($this->returnData(['msg'=>$this->language->get('error_filetype')]));
            }

            // Return any upload error
            if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                return $this->response->setOutput($this->returnData(['msg'=>$this->language->get('error_upload_' . $this->request->files['file']['error'])]));
            }
        } else {
            return $this->response->setOutput($this->returnData(['msg'=>$this->language->get('error_upload')]));
        }

        $file = $filename . '.' . token(32);

        //文件名扰码由扩展名后改为扩展名前
        $file = 'temp' . '.' . utf8_substr(mb_strrchr($filename, '.'), 1);

        move_uploaded_file($this->request->files['file']['tmp_name'], DIR_IMAGE . $dirname . $file);

        $img = $this->resize($dirname . $file, 600, 600,trim($dirname,'/'));
        unlink(DIR_IMAGE . $dirname . $file);

        if ($this->request->server['HTTPS']) {
            $json['imgurl'] =  $this->config->get('config_ssl') . 'image/' . $img;
        } else {
            $json['imgurl'] =  $this->config->get('config_url') . 'image/' . $img;
        }

        $json['code'] = md5($img);

        return $this->response->setOutput($this->returnData(['code'=>'200','msg'=>$this->language->get('text_upload'),'data'=>$json]));
    }

    public function resize($filename, $width, $height,$dirname) 
    {
        if (!is_file(DIR_IMAGE . $filename)) return;

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $image_old = $filename;
        $image_new = $dirname . '/' . $dirname . '-image-' . time() . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

        if (!is_file(DIR_IMAGE . $image_new))
        {
            list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
                 
            if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) { 
                return DIR_IMAGE . $image_old;
            }
                        
            $path = '';

            $directories = explode('/', dirname($image_new));

            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;

                if (!is_dir(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }

            if ($width_orig != $width || $height_orig != $height) {
                $image = new Image(DIR_IMAGE . $image_old);
                $image->resize($width, $height);
                $image->save(DIR_IMAGE . $image_new);
            } else {
                copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
            }
        }
        
        $image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +

        return $image_new;
    }
}