<?php
namespace App\Livewire\Concerns;

use Illuminate\Support\Str;
use App\Models\Tenant\Tag;

trait ManagesStudentTags
{
    public string $tagQuery = '';
    public array $tagSuggestions = [];
    public array $selectedTags = [];

    public function updatedTagQuery(): void
    {
        $q = trim($this->tagQuery);
        if ($q === '') { $this->tagSuggestions = []; return; }
        $like = '%'.str_replace(['%','_'],['\%','\_'],$q).'%';
        $this->tagSuggestions = Tag::query()
            ->where(fn($qq)=>$qq->where('name','like',$like)->orWhere('code','like',$like))
            ->orderBy('name')->limit(8)->get(['id','name','code','color'])->map->toArray()->all();
    }
    public function selectTag(int $id): void
    {
        if (collect($this->selectedTags)->firstWhere('id',$id)) return;
        if ($tag = Tag::find($id,['id','name','code','color'])) $this->selectedTags[] = $tag->toArray();
        $this->tagQuery=''; $this->tagSuggestions=[];
    }
    public function addTagFromQuery(): void
    {
        $name = trim($this->tagQuery); if ($name==='') return;
        $codeBase = Str::slug($name);
        $existing = Tag::where('name',$name)->orWhere('code',$codeBase)->first(['id','name','code','color']);
        if ($existing) { if(!collect($this->selectedTags)->firstWhere('id',$existing->id)) $this->selectedTags[]=$existing->toArray();
            $this->tagQuery=''; $this->tagSuggestions=[]; return; }
        $palette=['#10B981','#3B82F6','#F59E0B','#EF4444','#8B5CF6','#06B6D4','#84CC16','#EC4899'];
        $color=$palette[array_rand($palette)]; $base=$codeBase!==''?$codeBase:'tag'; $code=$base; $n=2;
        while (Tag::withTrashed()->where('code',$code)->exists()) { $code=$base.'-'.$n++; }
        $tag = Tag::create(['name'=>$name,'code'=>$code,'color'=>$color,'is_active'=>true]);
        $this->selectedTags[] = $tag->only(['id','name','code','color']);
        $this->tagQuery=''; $this->tagSuggestions=[];
    }
    public function removeTag(int $id): void
    { $this->selectedTags = array_values(array_filter($this->selectedTags, fn($t)=>(int)$t['id']!==$id)); }
}
