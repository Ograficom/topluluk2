<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

class StitchPhoto360 implements ShouldQueue
{
    use Queueable;

    public int $tries=1;
    public int $timeout=720;
    public bool $failOnTimeout=true;

    public function __construct(public string $id) {}

    public function handle(): void
    {
        $dir=storage_path('app/private/photo360/'.$this->id);
        $claimed=DB::table('photo360_jobs')->where('id',$this->id)->where('state','queued')
            ->update(['state'=>'processing','message'=>'Fotoğraflar kontrol ediliyor.','updated_at'=>now()]);
        if (!$claimed) return;
        $process=new Process(['/usr/bin/python3',base_path('scripts/photo360_hugin.py'),$dir],$dir,[
            'LC_ALL'=>'C','OMP_NUM_THREADS'=>'2','OPENBLAS_NUM_THREADS'=>'1',
        ]);
        $process->setTimeout(660);
        try {
            $process->start();
            $pending='';
            while ($process->isRunning()) {
                if (DB::table('photo360_jobs')->where('id',$this->id)->value('state')!=='processing') {
                    $process->stop(1);
                    File::deleteDirectory($dir);
                    return;
                }
                $pending.=$process->getIncrementalOutput();
                while (($pos=strpos($pending,"\n"))!==false) {
                    $line=substr($pending,0,$pos);$pending=substr($pending,$pos+1);
                    $event=json_decode($line,true);
                    if (is_string($event['message']??null)) {
                        DB::table('photo360_jobs')->where('id',$this->id)->where('state','processing')
                            ->update(['message'=>mb_substr($event['message'],0,255),'updated_at'=>now()]);
                    }
                }
                $process->checkTimeout();
                usleep(500000);
            }
            if (!$process->isSuccessful()||!is_file($dir.'/result.jpg')) {
                $failure=is_file($dir.'/failure.txt')?trim(File::get($dir.'/failure.txt')):'360° işleme başarısız oldu. Daha aydınlık ortamda yeniden çekin.';
                throw new \RuntimeException($failure);
            }
            $qualityPath=$dir.'/quality.json';
            if (is_file($qualityPath)) {
                $quality=json_decode(File::get($qualityPath),true);
                if (is_array($quality)) {
                    \Illuminate\Support\Facades\Log::info('photo360 performance', [
                        'job_id'=>$this->id,
                        'metrics'=>array_intersect_key($quality,array_flip([
                            'elapsed_seconds','stage_seconds','matching_strategy','threads',
                            'source_size','proxy_size','mean_error_px','maximum_error_px','control_points',
                        ])),
                    ]);
                }
            }
            DB::table('photo360_jobs')->where('id',$this->id)->where('state','processing')
                ->update(['state'=>'ready','message'=>'360° fotoğraf hazır. Önizlemeyi kontrol edin.','updated_at'=>now()]);
        } catch (Throwable $e) {
            if ($process->isRunning()) $process->stop(1);
            $diagnosticPath=$dir.'/diagnostics.json';
            $diagnostics=is_file($diagnosticPath)?json_decode(File::get($diagnosticPath),true):null;
            \Illuminate\Support\Facades\Log::warning('photo360 failure',[
                'job_id'=>$this->id,
                'reason'=>mb_substr($e->getMessage(),0,500),
                'diagnostics'=>is_array($diagnostics)?$diagnostics:null,
            ]);
            report($e);
            $this->failed($e);
        } finally {
            // Raw frames are private and are retained only until the result is produced.
            // On cancellation/failure or success, discard every intermediate.
            if (is_dir($dir)) foreach (glob($dir.'/*') ?: [] as $path) {
                if (basename($path)!=='result.jpg') {
                    is_dir($path)?File::deleteDirectory($path):File::delete($path);
                }
            }
            if (DB::table('photo360_jobs')->where('id',$this->id)->value('state')==='cancelled') File::deleteDirectory($dir);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $message='360° birleştirme tamamlanamadı. Aydınlık ortamda, aynı noktadan yeniden çekin.';
        $dir=storage_path('app/private/photo360/'.$this->id);
        if (is_file($dir.'/failure.txt')) $message=mb_substr(trim(File::get($dir.'/failure.txt')),0,255);
        DB::table('photo360_jobs')->where('id',$this->id)->whereIn('state',['queued','processing'])
            ->update(['state'=>'failed','message'=>$message,'updated_at'=>now()]);
    }
}
