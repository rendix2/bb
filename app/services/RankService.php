<?php declare(strict_types=1);

namespace App\services;

use App\Model\Repository\RankRepository;
use App\Settings\Ranks;
use Nette\Http\FileUpload;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use SplFileInfo;
use Tracy\Debugger;

class RankService
{

    public function __construct(
        private readonly Ranks $ranks,

        private readonly RankRepository $rankRepository
    )
    {
    }

    public function moveRank(FileUpload $file, int $id): string
    {
        if ($file->ok) {
            $rank = $this->rankRepository->findOneBy(
                [
                    'id' => $id
                ]
            );

            if ($rank && $rank->rank_file) {
                $this->removeRankFile($rank->rank_file);
            }

            $extension = $this->getFileExtension($file->name);
            $hash      = Random::generate(32);
            $name      = $hash . '.' . $extension;

            $file->move($this->ranks->getDir() . DIRECTORY_SEPARATOR . $name);

            return $name;
        } else {
            return self::NOT_UPLOADED;
        }
    }

    public function removeRankFile(string $rank_file)
    {
        try {
            FileSystem::delete($this->ranks->getDir() . DIRECTORY_SEPARATOR . $rank_file);

            return true;
        } catch (IOException $e) {
            Debugger::log(sprintf('File %s was not deleted.', $this->ranks->getDir() . DIRECTORY_SEPARATOR . $rank_file));
            return false;
        }
    }

    private function getFileExtension($fileName): string
    {
        $file = new SplFileInfo($fileName);

        return $file->getExtension();
    }

}