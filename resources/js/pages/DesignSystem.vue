<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';
import {
    Avatar,
    AvatarFallback,
    AvatarImage,
} from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Design System', href: '/design-system' }],
    },
});

const brandColors = [
    { name: 'Amarelo Horizon', hex: '#FBE509', swatch: 'bg-horizon-yellow', dark: false },
    { name: 'Preto Horizon', hex: '#000000', swatch: 'bg-horizon-black', dark: true },
    { name: 'Grafite', hex: '#141414', swatch: 'bg-horizon-graphite', dark: true },
    { name: 'Cinza claro', hex: '#F7F7F7', swatch: 'bg-horizon-gray-light', dark: false },
    { name: 'Branco', hex: '#FFFFFF', swatch: 'bg-horizon-white', dark: false },
];

const semanticColors = [
    { name: 'primary', label: 'Primary / CTA', classes: 'bg-primary text-primary-foreground' },
    { name: 'secondary', label: 'Secondary', classes: 'bg-secondary text-secondary-foreground' },
    { name: 'accent', label: 'Accent', classes: 'bg-accent text-accent-foreground' },
    { name: 'muted', label: 'Muted', classes: 'bg-muted text-muted-foreground' },
    { name: 'destructive', label: 'Destructive', classes: 'bg-destructive text-white' },
    { name: 'sidebar', label: 'Sidebar', classes: 'bg-sidebar text-sidebar-foreground' },
];
</script>

<template>
    <Head title="Design System" />

    <div class="flex h-full flex-1 flex-col gap-10 overflow-x-auto rounded-xl p-4 pb-16">
        <Heading
            title="Design System — Horizon"
            description="Biblioteca de componentes (shadcn-vue) com a identidade visual da marca Horizon aplicada aos tokens de tema."
        />

        <!-- Brand palette -->
        <section class="space-y-4">
            <Heading variant="small" title="Paleta de marca" description="As 5 cores oficiais Horizon." />
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <div
                    v-for="color in brandColors"
                    :key="color.hex"
                    class="overflow-hidden rounded-xl border"
                >
                    <div
                        :class="[color.swatch, 'flex h-20 items-end p-3']"
                    >
                        <span
                            :class="[
                                'text-xs font-medium',
                                color.dark ? 'text-white/70' : 'text-black/60',
                            ]"
                        >
                            Aa
                        </span>
                    </div>
                    <div class="space-y-0.5 bg-card p-3">
                        <p class="text-sm font-medium">{{ color.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ color.hex }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Semantic tokens -->
        <section class="space-y-4">
            <Heading
                variant="small"
                title="Tokens semânticos"
                description="Como a paleta Horizon foi mapeada para os tokens de tema (claro/escuro) usados pelos componentes."
            />
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <div
                    v-for="token in semanticColors"
                    :key="token.name"
                    class="overflow-hidden rounded-xl border"
                >
                    <div :class="[token.classes, 'flex h-16 items-end p-3']">
                        <span class="text-xs font-medium opacity-70">Aa</span>
                    </div>
                    <div class="bg-card p-2.5">
                        <p class="text-xs font-medium">{{ token.label }}</p>
                        <code class="text-[11px] text-muted-foreground">--{{ token.name }}</code>
                    </div>
                </div>
            </div>
        </section>

        <Separator />

        <!-- Typography -->
        <section class="space-y-4">
            <Heading variant="small" title="Tipografia" />
            <Card>
                <CardContent class="space-y-3">
                    <h1 class="text-4xl font-semibold tracking-tight">Heading 1 — Horizon</h1>
                    <h2 class="text-2xl font-semibold tracking-tight">Heading 2 — Horizon</h2>
                    <h3 class="text-xl font-semibold tracking-tight">Heading 3 — Horizon</h3>
                    <p class="text-base text-foreground">
                        Texto padrão (foreground) sobre fundo Branco Horizon — usado em corpo de texto e parágrafos.
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Texto secundário (muted-foreground) — legendas, descrições e metadados.
                    </p>
                    <a href="#" class="text-sm font-medium text-primary underline-offset-4 hover:underline">
                        Link de texto usando a cor primária
                    </a>
                </CardContent>
            </Card>
        </section>

        <!-- Buttons -->
        <section class="space-y-4">
            <Heading variant="small" title="Botões" description="Variantes disponíveis em components/ui/button." />
            <Card>
                <CardContent class="flex flex-wrap items-center gap-3">
                    <Button>Primary</Button>
                    <Button variant="secondary">Secondary</Button>
                    <Button variant="outline">Outline</Button>
                    <Button variant="ghost">Ghost</Button>
                    <Button variant="link">Link</Button>
                    <Button variant="destructive">Destructive</Button>
                </CardContent>
                <CardContent class="flex flex-wrap items-center gap-3 pt-0">
                    <Button size="sm">Small</Button>
                    <Button size="default">Default</Button>
                    <Button size="lg">Large</Button>
                    <Button disabled>Disabled</Button>
                </CardContent>
            </Card>
        </section>

        <!-- Badges -->
        <section class="space-y-4">
            <Heading variant="small" title="Badges" />
            <Card>
                <CardContent class="flex flex-wrap items-center gap-3">
                    <Badge>Default</Badge>
                    <Badge variant="secondary">Secondary</Badge>
                    <Badge variant="outline">Outline</Badge>
                    <Badge variant="destructive">Destructive</Badge>
                </CardContent>
            </Card>
        </section>

        <!-- Alerts -->
        <section class="space-y-4">
            <Heading variant="small" title="Alertas" />
            <div class="grid gap-4 md:grid-cols-2">
                <Alert>
                    <AlertTitle>Tudo certo</AlertTitle>
                    <AlertDescription>
                        Mensagem informativa usando as cores neutras do tema.
                    </AlertDescription>
                </Alert>
                <Alert variant="destructive">
                    <AlertTitle>Algo deu errado</AlertTitle>
                    <AlertDescription>
                        Mensagem de erro usando a cor destrutiva do sistema.
                    </AlertDescription>
                </Alert>
            </div>
        </section>

        <!-- Forms -->
        <section class="space-y-4">
            <Heading variant="small" title="Formulário" />
            <Card class="max-w-md">
                <CardContent class="space-y-4">
                    <div class="space-y-1.5">
                        <Label for="ds-name">Nome</Label>
                        <Input id="ds-name" placeholder="Digite seu nome" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="ds-email">E-mail</Label>
                        <Input id="ds-email" type="email" placeholder="voce@horizon.com" />
                    </div>
                    <div class="flex items-center gap-2">
                        <Checkbox id="ds-terms" />
                        <Label for="ds-terms" class="font-normal">Aceito os termos</Label>
                    </div>
                    <Button class="w-full">Enviar</Button>
                </CardContent>
            </Card>
        </section>

        <!-- Cards & Avatar -->
        <section class="space-y-4">
            <Heading variant="small" title="Cards & Avatar" />
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader class="flex-row items-center gap-3 space-y-0">
                        <Avatar>
                            <AvatarImage src="" alt="Horizon" />
                            <AvatarFallback class="bg-primary text-primary-foreground">
                                H
                            </AvatarFallback>
                        </Avatar>
                        <div>
                            <CardTitle>Equipe Horizon</CardTitle>
                            <CardDescription>Exemplo de card com avatar</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent class="text-sm text-muted-foreground">
                        Componente Card reaproveitado do starter kit, sem alterações de estrutura —
                        apenas os tokens de cor foram atualizados.
                    </CardContent>
                </Card>
                <Card class="border-primary">
                    <CardHeader>
                        <CardTitle>Card em destaque</CardTitle>
                        <CardDescription>Usando borda com a cor primária</CardDescription>
                    </CardHeader>
                    <CardContent class="flex items-center gap-2">
                        <Badge>Novo</Badge>
                        <span class="text-sm text-muted-foreground">Aplicado com border-primary</span>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>
