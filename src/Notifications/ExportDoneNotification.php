<?php

namespace LaravelEnso\Tables\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class ExportDoneNotification extends Notification implements ShouldQueue
{
    use Dispatchable, Queueable;

    private string $filePath;
    private string $filename;
    private $dataExport;
    private $entries;
    private ?string $link;

    public function __construct(string $filePath, string $filename, $dataExport, $entries)
    {
        $this->filePath = $filePath;
        $this->filename = $filename;
        $this->dataExport = $dataExport;
        $this->link = optional($this->dataExport)->temporaryLink();
        $this->entries = $entries;
    }

    public function via()
    {
        return Config::get('enso.tables.export.notifications');
    }

    public function toBroadcast()
    {
        return (new BroadcastMessage($this->toArray() + [
            'level' => 'success',
            'title' => __('Export Done'),
        ]))->onQueue($this->queue);
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage())
            ->subject(__(Config::get('app.name')).': '.__('Table Export Notification'))
            ->markdown('laravel-enso/tables::emails.export', [
                'name' => optional($notifiable->person)->appellative(),
                'filename' => __($this->filename),
                'entries' => $this->entries,
                'link' => $this->link,
            ]);

        if (! $this->link) {
            $mail->attach($this->filePath);
        }

        return $mail;
    }

    public function toArray()
    {
        return [
            'body' => $this->link
                ? __('Export available for download').': '.__($this->filename)
                : __('Export emailed').': '.__($this->filename),
            'icon' => 'file-excel',
            'path' => $this->link ? '/files' : null,
        ];
    }
}
