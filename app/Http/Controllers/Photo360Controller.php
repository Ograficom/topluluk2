<?php

namespace App\Http\Controllers;

use App\Jobs\StitchPhoto360;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Photo360Controller extends Controller
{
    private function directory(string $id): string
    {
        return storage_path('app/private/photo360/'.$id);
    }

    private function owned(Request $request, string $id, bool $lock = false): object
    {
        $query = DB::table('photo360_jobs')->where('id', $id)->where('user_id', $request->user()->id);
        $job = ($lock ? $query->lockForUpdate() : $query)->first();
        abort_unless($job, 404);
        return $job;
    }

    private function ensureEngine(): void
    {
        foreach (['pto_gen','pto_var','cpfind','cpclean','autooptimiser','checkpto','pano_modify','nona','enblend','python3'] as $binary) {
            abort_unless(is_executable('/usr/bin/'.$binary), 503, '360° işleme motoru sunucuda henüz kurulu değil.');
        }
    }

    public function health()
    {
        $this->ensureEngine();
        return response()->json(['available'=>true])->header('Cache-Control','private, no-store');
    }

    public function create(Request $request)
    {
        $data = $request->validate(['count' => 'required|integer|min:24|max:48']);
        $this->ensureEngine();
        $id = (string) Str::uuid();
        DB::transaction(function () use ($request, $data, $id): void {
            DB::table('users')->where('id', $request->user()->id)->lockForUpdate()->first();
            $active = DB::table('photo360_jobs')->where('user_id', $request->user()->id)
                ->whereIn('state', ['uploading','queued','processing'])->where('updated_at','>',now()->subMinutes(30))->exists();
            abort_if($active, 409, 'Başka bir 360° işleminiz devam ediyor.');
            File::ensureDirectoryExists($this->directory($id), 0700, true);
            DB::table('photo360_jobs')->insert(['id'=>$id,'user_id'=>$request->user()->id,'expected'=>$data['count'],
                'state'=>'uploading','message'=>'Kareler yükleniyor.','created_at'=>now(),'updated_at'=>now()]);
        });
        return response()->json(['id'=>$id], 201);
    }

    public function frame(Request $request, string $id)
    {
        $this->owned($request, $id);
        $data = $request->validate([
            'index'=>'required|integer|min:0|max:47',
            'frame'=>'required|file|mimes:jpg,jpeg|max:3072|dimensions:min_width=320,min_height=320,max_width=2560,max_height=2560',
            'yaw'=>'required|numeric|between:-180,180',
            'pitch'=>'required|numeric|between:-90,90',
            'roll'=>'required|numeric|between:-180,180',
        ]);
        return DB::transaction(function () use ($request, $id, $data) {
            $job=$this->owned($request,$id,true);
            abort_unless($job->state==='uploading',409);
            abort_unless($data['index']<$job->expected,422);
            $dir=$this->directory($id);
            $stem=sprintf('frame-%03d',$data['index']);
            $file=$dir.'/'.$stem.'.jpg'; $meta=$dir.'/'.$stem.'.json';
            if (!is_file($file)||!is_file($meta)) {
                $data['frame']->move($dir,$stem.'.jpg');
                $written=File::put($meta,json_encode(['yaw'=>(float)$data['yaw'],'pitch'=>(float)$data['pitch'],'roll'=>(float)$data['roll']], JSON_THROW_ON_ERROR),true);
                abort_unless($written!==false,500,'Çekim kaydedilemedi.');
            }
            $received=count(glob($dir.'/frame-*.json'));
            DB::table('photo360_jobs')->where('id',$id)->update(['received'=>$received,'updated_at'=>now()]);
            return response()->json(['received'=>$received]);
        });
    }

    public function start(Request $request,string $id)
    {
        DB::transaction(function () use ($request,$id): void {
            $job=$this->owned($request,$id,true);
            if (in_array($job->state,['queued','processing','ready'],true)) return;
            abort_unless($job->state==='uploading'&&(int)$job->received===(int)$job->expected,422,'Tüm kareler yüklenmedi.');
            DB::table('photo360_jobs')->where('id',$id)->update(['state'=>'queued','message'=>'İşleme sırası bekleniyor.','updated_at'=>now()]);
            StitchPhoto360::dispatch($id)->onConnection('photo360')->onQueue('photo360')->afterCommit();
        });
        return response()->json(['accepted'=>true],202);
    }

    public function status(Request $request,string $id)
    {
        $job=$this->owned($request,$id);
        return response()->json(['state'=>$job->state,'message'=>$job->message])->header('Cache-Control','private, no-store');
    }

    public function image(Request $request,string $id)
    {
        $job=$this->owned($request,$id);
        $file=$this->directory($id).'/result.jpg';
        abort_unless($job->state==='ready'&&is_file($file),404);
        return response()->file($file,['Content-Type'=>'image/jpeg','Cache-Control'=>'private, no-store']);
    }

    public function cancel(Request $request,string $id)
    {
        DB::transaction(function () use ($request,$id): void {
            $job=$this->owned($request,$id,true);
            if ($job->state!=='processing') {
                File::deleteDirectory($this->directory($id));
            }
            DB::table('photo360_jobs')->where('id',$id)->update(['state'=>'cancelled','message'=>'İptal edildi.','updated_at'=>now()]);
        });
        return response()->noContent();
    }
}
