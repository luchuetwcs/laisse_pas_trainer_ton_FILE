<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 11/10/17
 * Time: 16:07
 */

namespace Controller;

use Model\Item;
use Model\ItemManager;

/**
 * Class FichiersController
 * @package Controller
 */
class FichiersController extends AbstractController
{

    /**
     * @return string
     */
    public function index()
    {
        echo (__DIR__);
        //récupération des informations sur les fichiers du dossier upload
        $scanUploads = scandir(UPLOAD_DIR);
        //on enleve les cases du tableau qui ne servent qu'a naviguer dans le systeme de fichiers
        unset($scanUploads[array_search('.', $scanUploads)]);
        unset($scanUploads[array_search('..', $scanUploads)]);
        if (count($scanUploads) === 0) {
            return $this->twig->render('Fichiers/index.html.twig', [
                'files' => $this->files,
                'errors' => $this->errors,
            ]);
        } else {
            foreach ($scanUploads as $upload) {
                $file = [];
                $file['name'] = $upload;
                $file['image'] = 'upload/' . $upload;
                $this->uploads[] = $file;
            }
            return $this->twig->render('Fichiers/index.html.twig', [
                'files' => $this->files,
                'errors' => $this->errors,
                'uploads' => $this->uploads,
            ]);
        }
    }

    /**
     * @param $id
     * @return string
     */
    public function add()
    {
        $files = $_FILES['files'];
        if ($files['error'][0] === 4) {
            //erreur 4 => UPLOAD_ERR_NO_FILE, aucun fichier n'a été téléchargé
            $this->errors[] = 'Il faut sélectionner au moins 1 fichier.';
        } else {
            //traitement des fichiers
            $uploadFiles = [];
            for ($i = 0; $i < count($files['name']); $i++) {
                $file = [];
                $file['name'] = $files['name'][$i];
                $file['type'] = $files['type'][$i];
                $file['tmp_name'] = $files['tmp_name'][$i];
                $file['error'] = $files['error'][$i];
                $file['size'] = $files['size'][$i];
                $infoName = pathinfo($file['name']);
                $extension = '.' . $infoName['extension'];
                $file['upload_dir'] = UPLOAD_DIR . 'image' . uniqid() . $extension;
                $uploadFiles[] = $file;
            }
            //tests sur les fichiers
            foreach ($uploadFiles as $uploadFile) {
                $error = false;
                if ($uploadFile['size'] > 1024000) {
                    $this->errors[] = 'Le fichier ' . $file['name'] . ' est trop volumineux.';
                    $error = true;
                }
                if (!in_array($uploadFile['type'], ['image/gif', 'image/jpeg', 'image/png'])) {
                    $this->errors[] = 'Le type du fichier n\'est pas jpg, png ou gif.';
                    $error = true;
                }
                if (!$error) {
                    move_uploaded_file($uploadFile['tmp_name'], $uploadFile['upload_dir']);
                }
            }
        }
        return $this->index();
    }

    /**
     * @param $id
     * @return string
     */
    public function delete()
    {
        echo "Dans le delete";
        if (isset($_POST['delete'])) {
            $fileToDelete = 'upload/' . $_POST['delete'];
            echo $fileToDelete;
            if (file_exists($fileToDelete)) {
                unlink($fileToDelete);
            }
        }
        header('Location: /');
    }
}