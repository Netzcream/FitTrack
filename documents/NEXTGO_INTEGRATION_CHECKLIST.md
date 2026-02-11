# Next.go - Checklist de Integración

## Preparación
- [ ] Backend FitTrack activo
- [ ] Variables de entorno configuradas (`NEXT_PUBLIC_API_URL`)

## Cliente API
- [ ] Crear cliente HTTP central
- [ ] Inyectar `Authorization` y `X-Tenant-ID`

## Autenticación
- [ ] Login con `/api/auth/login`
- [ ] Persistir `token` y `tenant.id`
- [ ] Logout con `/api/auth/logout`

## Flujos esenciales
- [ ] Perfil: `GET /api/profile` y `PATCH /api/profile`
- [ ] Plan activo: `GET /api/plans/current`
- [ ] Workouts: `GET /api/workouts/today`, start, update, complete
- [ ] Peso: `GET /api/weight` y `POST /api/weight`
- [ ] Mensajes: `GET /api/messages/conversation`, `POST /api/messages/send`

## Branding
- [ ] Aplicar colores/logo desde `branding` en cada respuesta

## Validación rápida
- [ ] Login funciona
- [ ] Header `X-Tenant-ID` correcto
- [ ] Workouts completos# 🚀 Next.go Integration Checklist

Checklist paso a paso para integrar la API FitTrack en tu app Next.go.

---

## ✅ Pre-Requisitos

- [ ] Node.js 18+ instalado
- [ ] Next.js 14+ configurado
- [ ] FitTrack backend en localhost:8000
- [ ] Postman o Thunder Client para testing
- [ ] Un estudiante de prueba creado en FitTrack

---

## 🔧 Setup del Proyecto

### 1. Instalar Dependencias
```bash
npm install axios zustand react-query
```

- `axios`: Cliente HTTP
- `zustand`: State management simple
- `react-query`: Caching de datos de API

### 2. Variables de Entorno (.env.local)
```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_APP_NAME=FitTrack Student
```

### 3. Crear estructura de carpetas
```
src/
├── lib/
│   ├── api.ts              # Cliente Axios configurado
│   └── constants.ts        # URLs, durations, etc
├── services/
│   ├── auth.service.ts     # Login/logout
│   ├── workouts.service.ts # Workouts
│   ├── plans.service.ts    # Training plans
│   ├── weight.service.ts   # Weight tracking
│   └── progress.service.ts # Progress
├── hooks/
│   ├── useAuth.ts          # Auth state
│   ├── useWorkouts.ts      # Workouts queries
│   └── useBranding.ts      # Branding state
├── types/
│   └── api.ts              # TypeScript interfaces
└── components/
    ├── auth/
    │   └── LoginForm.tsx
    └── workouts/
        ├── WorkoutCard.tsx
        └── WorkoutTracker.tsx
```

---

## 🔐 Autenticación

### 1. Crear tipo TypeScript
```typescript
// src/types/api.ts

export interface LoginResponse {
  token: string;
  user: {
    id: number;
    email: string;
  };
  student: Student;
  tenant: Tenant;
  branding: Branding;
}

export interface Student {
  uuid: string;
  email: string;
  first_name: string;
  last_name: string;
  full_name: string;
  goal: string;
  // ... más campos
}

export interface Branding {
  brand_name: string;
  trainer_name: string;
  trainer_email: string;
  logo_url: string;
  logo_light_url: string;
  primary_color: string;
  secondary_color: string;
  accent_color: string;
}
```

### 2. Crear cliente API
```typescript
// src/lib/api.ts

import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL,
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('fittrack_token');
  const tenantId = localStorage.getItem('fittrack_tenant_id');

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  if (tenantId) {
    config.headers['X-Tenant-ID'] = tenantId;
  }

  return config;
});

export default api;
```

### 3. Crear hook de autenticación
```typescript
// src/hooks/useAuth.ts

import { create } from 'zustand';
import api from '@/lib/api';

interface AuthStore {
  token: string | null;
  student: Student | null;
  branding: Branding | null;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  isLoggedIn: () => boolean;
}

export const useAuth = create<AuthStore>((set) => ({
  token: localStorage.getItem('fittrack_token'),
  student: localStorage.getItem('fittrack_student') 
    ? JSON.parse(localStorage.getItem('fittrack_student')!) 
    : null,
  branding: localStorage.getItem('fittrack_branding')
    ? JSON.parse(localStorage.getItem('fittrack_branding')!)
    : null,

  login: async (email, password) => {
    const res = await api.post('/auth/login', { email, password });
    const { token, student, tenant, branding } = res.data;

    localStorage.setItem('fittrack_token', token);
    localStorage.setItem('fittrack_tenant_id', tenant.id);
    localStorage.setItem('fittrack_student', JSON.stringify(student));
    localStorage.setItem('fittrack_branding', JSON.stringify(branding));

    set({ token, student, branding });
  },

  logout: async () => {
    await api.post('/auth/logout');
    localStorage.clear();
    set({ token: null, student: null, branding: null });
  },

  isLoggedIn: () => !!localStorage.getItem('fittrack_token'),
}));
```

### 4. Crear LoginForm
```typescript
// src/components/auth/LoginForm.tsx

'use client';

import { useState } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';

export default function LoginForm() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const { login } = useAuth();
  const router = useRouter();

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      await login(email, password);
      router.push('/dashboard');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleLogin} className="space-y-4">
      <div>
        <label className="block text-sm font-medium">Email</label>
        <input
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
          className="w-full border rounded px-3 py-2"
        />
      </div>

      <div>
        <label className="block text-sm font-medium">Password</label>
        <input
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
          className="w-full border rounded px-3 py-2"
        />
      </div>

      {error && <div className="text-red-600 text-sm">{error}</div>}

      <button
        type="submit"
        disabled={loading}
        className="w-full bg-blue-600 text-white rounded px-4 py-2 hover:bg-blue-700 disabled:opacity-50"
      >
        {loading ? 'Logging in...' : 'Login'}
      </button>
    </form>
  );
}
```

---

## 📋 Planes de Entrenamiento

### 1. Servicio
```typescript
// src/services/plans.service.ts

import api from '@/lib/api';

export const plansService = {
  getCurrent: async () => {
    const res = await api.get('/plans/current');
    return res.data.data;
  },

  getAll: async () => {
    const res = await api.get('/plans');
    return res.data.data;
  },

  getById: async (id: string | number) => {
    const res = await api.get(`/plans/${id}`);
    return res.data.data;
  },
};
```

### 2. Componente
```typescript
// src/components/plans/CurrentPlanCard.tsx

'use client';

import { useEffect, useState } from 'react';
import { plansService } from '@/services/plans.service';

export default function CurrentPlanCard() {
  const [plan, setPlan] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    plansService.getCurrent()
      .then(setPlan)
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div>Loading...</div>;
  if (!plan) return <div>No active plan</div>;

  return (
    <div className="p-6 border rounded-lg bg-white">
      <h2 className="text-2xl font-bold">{plan.name}</h2>
      <p className="text-gray-600">{plan.description}</p>
      <p className="mt-4">
        Goal: <strong>{plan.goal}</strong>
      </p>
      <p className="text-sm text-gray-500">
        {plan.assigned_from} to {plan.assigned_until}
      </p>
    </div>
  );
}
```

---

## 💪 Workouts

### 1. Servicio
```typescript
// src/services/workouts.service.ts

import api from '@/lib/api';

export const workoutsService = {
  getToday: async () => {
    const res = await api.get('/workouts/today');
    return res.data.data;
  },

  getAll: async (status?: string) => {
    const res = await api.get('/workouts', {
      params: { status },
    });
    return res.data.data;
  },

  start: async (id: number) => {
    const res = await api.post(`/workouts/${id}/start`);
    return res.data.data;
  },

  updateExercises: async (id: number, exercises: any[]) => {
    const res = await api.patch(`/workouts/${id}`, { exercises });
    return res.data.data;
  },

  complete: async (id: number, data: any) => {
    const res = await api.post(`/workouts/${id}/complete`, data);
    return res.data.data;
  },

  getStats: async () => {
    const res = await api.get('/workouts/stats');
    return res.data.data;
  },
};
```

### 2. Componente (Workout Tracker)
```typescript
// src/components/workouts/WorkoutTracker.tsx

'use client';

import { useEffect, useState } from 'react';
import { workoutsService } from '@/services/workouts.service';

export default function WorkoutTracker() {
  const [workout, setWorkout] = useState(null);
  const [loading, setLoading] = useState(true);
  const [startTime, setStartTime] = useState<Date | null>(null);

  useEffect(() => {
    loadWorkout();
  }, []);

  const loadWorkout = async () => {
    try {
      const data = await workoutsService.getToday();
      setWorkout(data);
    } finally {
      setLoading(false);
    }
  };

  const handleStart = async () => {
    if (!workout) return;
    setStartTime(new Date());
    const updated = await workoutsService.start(workout.id);
    setWorkout(updated);
  };

  const handleComplete = async () => {
    if (!workout || !startTime) return;

    const duration = Math.round(
      (new Date().getTime() - startTime.getTime()) / 60000
    );

    const data = await workoutsService.complete(workout.id, {
      duration_minutes: duration,
      rating: 5,
      survey: {
        fatigue: 3,
        rpe: 7,
      },
    });

    setWorkout(data);
    alert('Workout completed!');
  };

  if (loading) return <div>Loading...</div>;
  if (!workout) return <div>No workout available</div>;

  return (
    <div className="p-6 border rounded-lg">
      <h2 className="text-2xl font-bold">Day {workout.plan_day}</h2>

      <div className="mt-4 space-y-2">
        {workout.exercises.map((ex: any) => (
          <div key={ex.id} className="p-3 bg-gray-100 rounded">
            <strong>{ex.name}</strong>
            <p className="text-sm text-gray-600">{ex.sets} sets x {ex.reps} reps</p>
          </div>
        ))}
      </div>

      <div className="mt-6 space-x-2">
        {!workout.is_in_progress && (
          <button
            onClick={handleStart}
            className="px-4 py-2 bg-green-600 text-white rounded"
          >
            Start Workout
          </button>
        )}

        {workout.is_in_progress && (
          <button
            onClick={handleComplete}
            className="px-4 py-2 bg-blue-600 text-white rounded"
          >
            Complete ({startTime && Math.round((new Date().getTime() - startTime.getTime()) / 60000)}m)
          </button>
        )}
      </div>
    </div>
  );
}
```

---

## ⚖️ Tracking de Peso

### 1. Servicio
```typescript
// src/services/weight.service.ts

import api from '@/lib/api';

export const weightService = {
  getHistory: async (days?: number) => {
    const res = await api.get('/weight', {
      params: { days },
    });
    return res.data.data;
  },

  getLatest: async () => {
    const res = await api.get('/weight/latest');
    return res.data.data;
  },

  record: async (weight: number, notes?: string) => {
    const res = await api.post('/weight', {
      weight_kg: weight,
      source: 'manual',
      notes,
    });
    return res.data.data;
  },

  getChange: async (days: number = 7) => {
    const res = await api.get('/weight/change', {
      params: { days },
    });
    return res.data.data;
  },
};
```

### 2. Componente
```typescript
// src/components/weight/WeightTracker.tsx

'use client';

import { useEffect, useState } from 'react';
import { weightService } from '@/services/weight.service';

export default function WeightTracker() {
  const [weight, setWeight] = useState('');
  const [latest, setLatest] = useState(null);
  const [change, setChange] = useState(null);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    const [latestData, changeData] = await Promise.all([
      weightService.getLatest(),
      weightService.getChange(7),
    ]);
    setLatest(latestData);
    setChange(changeData);
  };

  const handleRecord = async () => {
    if (!weight) return;
    await weightService.record(parseFloat(weight));
    setWeight('');
    await loadData();
  };

  return (
    <div className="p-6 space-y-4">
      <div>
        <h3 className="font-bold">Record Weight</h3>
        <input
          type="number"
          value={weight}
          onChange={(e) => setWeight(e.target.value)}
          placeholder="kg"
          className="border rounded px-3 py-2"
          step="0.1"
        />
        <button
          onClick={handleRecord}
          className="ml-2 px-4 py-2 bg-blue-600 text-white rounded"
        >
          Save
        </button>
      </div>

      {latest && (
        <div>
          <p className="text-sm text-gray-600">Current Weight</p>
          <p className="text-2xl font-bold">{latest.weight_kg} kg</p>
        </div>
      )}

      {change && (
        <div>
          <p className="text-sm text-gray-600">Change (7 days)</p>
          <p className={`text-xl font-bold ${change.change_kg < 0 ? 'text-green-600' : 'text-red-600'}`}>
            {change.change_kg > 0 ? '+' : ''}{change.change_kg} kg ({change.change_percentage}%)
          </p>
        </div>
      )}
    </div>
  );
}
```

---

## 🎨 Branding

### 1. Hook para branding
```typescript
// src/hooks/useBranding.ts

import { useAuth } from './useAuth';

export function useBranding() {
  const { branding } = useAuth();

  return {
    brandName: branding?.brand_name,
    trainerName: branding?.trainer_name,
    logoUrl: branding?.logo_url,
    primaryColor: branding?.primary_color || '#3B82F6',
    secondaryColor: branding?.secondary_color || '#10B981',
    accentColor: branding?.accent_color || '#F59E0B',
  };
}
```

### 2. Aplicar colores en CSS
```typescript
// src/components/Layout.tsx

'use client';

import { useBranding } from '@/hooks/useBranding';
import { useEffect } from 'react';

export default function Layout({ children }: { children: React.ReactNode }) {
  const branding = useBranding();

  useEffect(() => {
    const root = document.documentElement;
    root.style.setProperty('--primary-color', branding.primaryColor);
    root.style.setProperty('--secondary-color', branding.secondaryColor);
    root.style.setProperty('--accent-color', branding.accentColor);
  }, [branding]);

  return (
    <div>
      <header className="bg-[var(--primary-color)] text-white p-4">
        {branding.logoUrl && (
          <img src={branding.logoUrl} alt="Logo" className="h-8" />
        )}
        <h1>{branding.brandName}</h1>
      </header>
      <main>{children}</main>
    </div>
  );
}
```

---

## 🧪 Testing

### 1. Test de Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student@trainer.com","password":"password"}'
```

### 2. Test de Workouts
```bash
curl -X GET http://localhost:8000/api/workouts/today \
  -H "Authorization: Bearer {token}" \
  -H "X-Tenant-ID: {tenant_id}"
```

### 3. Test de Branding
```bash
curl -X GET http://localhost:8000/api/profile \
  -H "Authorization: Bearer {token}" \
  -H "X-Tenant-ID: {tenant_id}" | jq '.branding'
```

---

## ✅ Checklist de Integración

### Autenticación
- [ ] Login funcional
- [ ] Logout funcional
- [ ] Token guardado correctamente
- [ ] Redirect después de login

### Planes
- [ ] Mostrar plan activo
- [ ] Listar todos los planes
- [ ] Ver detalles de plan

### Workouts
- [ ] Obtener workout del día
- [ ] Iniciar sesión
- [ ] Actualizar ejercicios
- [ ] Completar workout
- [ ] Ver estadísticas

### Peso
- [ ] Registrar peso
- [ ] Ver historial
- [ ] Ver cambio en período
- [ ] Ver promedio

### Branding
- [ ] Logo visible en header
- [ ] Colores aplicados correctamente
- [ ] Nombre del trainer visible
- [ ] Email de contacto disponible

### Testing
- [ ] Postman tests para cada endpoint
- [ ] Tests unitarios de servicios
- [ ] Tests de integración

---

## 📚 Recursos

- API Docs: [MOBILE_API_NEXTGO_COMPLETE.md](MOBILE_API_NEXTGO_COMPLETE.md)
- Changes: [API_CHANGES_SUMMARY.md](API_CHANGES_SUMMARY.md)
- Branding: [BRANDING_CONFIG_GUIDE.md](BRANDING_CONFIG_GUIDE.md)

---

**Última actualización:** Enero 2026
