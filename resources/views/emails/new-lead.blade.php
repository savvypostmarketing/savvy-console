<!DOCTYPE html>
<html lang="{{ $lead->locale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $lead->locale === 'es' ? 'Nuevo Lead' : 'New Lead' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            border-bottom: 3px solid #0066cc;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #0066cc;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .field {
            margin-bottom: 12px;
        }
        .field-label {
            font-weight: 600;
            color: #555;
            font-size: 13px;
            margin-bottom: 2px;
        }
        .field-value {
            color: #333;
            font-size: 15px;
        }
        .field-value a {
            color: #0066cc;
            text-decoration: none;
        }
        .services-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .services-list li {
            display: inline-block;
            background-color: #e8f4fc;
            color: #0066cc;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 13px;
            margin: 2px 4px 2px 0;
        }
        .message-box {
            background-color: #f9f9f9;
            border-left: 3px solid #0066cc;
            padding: 15px;
            font-style: italic;
            color: #555;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $lead->locale === 'es' ? 'Nuevo Lead Recibido' : 'New Lead Received' }}</h1>
            <p>{{ $lead->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <div class="section">
            <div class="section-title">{{ $lead->locale === 'es' ? 'Informacion de Contacto' : 'Contact Information' }}</div>

            <div class="field">
                <div class="field-label">{{ $lead->locale === 'es' ? 'Nombre' : 'Name' }}</div>
                <div class="field-value">{{ $lead->name ?? '-' }}</div>
            </div>

            <div class="field">
                <div class="field-label">Email</div>
                <div class="field-value">
                    @if($lead->email)
                        <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                    @else
                        -
                    @endif
                </div>
            </div>

            <div class="field">
                <div class="field-label">{{ $lead->locale === 'es' ? 'Empresa' : 'Company' }}</div>
                <div class="field-value">{{ $lead->company ?? '-' }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">{{ $lead->locale === 'es' ? 'Informacion del Negocio' : 'Business Information' }}</div>

            <div class="field">
                <div class="field-label">{{ $lead->locale === 'es' ? 'Industria' : 'Industry' }}</div>
                <div class="field-value">{{ $lead->industry_display ?? '-' }}</div>
            </div>

            <div class="field">
                <div class="field-label">{{ $lead->locale === 'es' ? 'Tiene Sitio Web' : 'Has Website' }}</div>
                <div class="field-value">
                    @if($lead->has_website === 'yes')
                        <span class="badge badge-success">{{ $lead->locale === 'es' ? 'Si' : 'Yes' }}</span>
                        @if($lead->website_url)
                            - <a href="{{ $lead->website_url }}" target="_blank">{{ $lead->website_url }}</a>
                        @endif
                    @else
                        <span class="badge badge-warning">No</span>
                    @endif
                </div>
            </div>
        </div>

        @if(!empty($lead->services))
        <div class="section">
            <div class="section-title">{{ $lead->locale === 'es' ? 'Servicios de Interes' : 'Services of Interest' }}</div>
            <ul class="services-list">
                @foreach($lead->services as $service)
                    <li>{{ $service }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($lead->message)
        <div class="section">
            <div class="section-title">{{ $lead->locale === 'es' ? 'Mensaje' : 'Message' }}</div>
            <div class="message-box">
                {{ $lead->message }}
            </div>
        </div>
        @endif

        @if(!empty($lead->discovery_answers))
        <div class="section">
            <div class="section-title">{{ $lead->locale === 'es' ? 'Como nos Encontro' : 'How They Found Us' }}</div>
            @foreach($lead->discovery_answers as $key => $value)
                <div class="field">
                    <div class="field-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                    <div class="field-value">{{ is_array($value) ? implode(', ', $value) : $value }}</div>
                </div>
            @endforeach
        </div>
        @endif

        <div class="section">
            <div class="section-title">{{ $lead->locale === 'es' ? 'Datos de Seguimiento' : 'Tracking Data' }}</div>

            @if($lead->utm_source || $lead->utm_medium || $lead->utm_campaign)
            <div class="field">
                <div class="field-label">UTM</div>
                <div class="field-value">
                    @if($lead->utm_source) Source: {{ $lead->utm_source }} @endif
                    @if($lead->utm_medium) | Medium: {{ $lead->utm_medium }} @endif
                    @if($lead->utm_campaign) | Campaign: {{ $lead->utm_campaign }} @endif
                </div>
            </div>
            @endif

            @if($lead->referrer)
            <div class="field">
                <div class="field-label">Referrer</div>
                <div class="field-value">{{ $lead->referrer }}</div>
            </div>
            @endif

            <div class="field">
                <div class="field-label">{{ $lead->locale === 'es' ? 'Idioma' : 'Language' }}</div>
                <div class="field-value">{{ $lead->locale === 'es' ? 'Espanol' : 'English' }}</div>
            </div>
        </div>

        <div class="footer">
            <p>{{ $lead->locale === 'es' ? 'Este email fue generado automaticamente' : 'This email was automatically generated' }} - Lead ID: {{ $lead->uuid }}</p>
        </div>
    </div>
</body>
</html>
