<script lang="ts" setup>
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import AppLogo from '@/components/shared/AppLogo.vue';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { LogIn } from '@lucide/vue';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
} from '@/components/ui/input-group';
import { MailIcon, Lock } from '@lucide/vue';
import { Form } from '@inertiajs/vue3';
</script>

<template>
    <Form
        #default="{ errors, processing }"
        action="/login"
        class="w-full max-w-sm"
        method="POST"
    >
        <Card class="bg-card">
            <CardHeader class="flex items-center justify-center">
                <AppLogo />
            </CardHeader>
            <CardHeader>
                <CardTitle>
                    <h1>Login</h1>
                </CardTitle>
                <CardDescription class="text-primary">
                    Enter your email and password below to login
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="grid w-full items-center gap-4">
                    <p
                        v-if="errors.failed"
                        class="text-sm text-center text-destructive"
                    >
                        {{ errors.failed }}
                    </p>
                    <div class="flex flex-col space-y-1.5">
                        <InputGroup>
                            <InputGroupInput
                                :aria-invalid="!!errors.failed"
                                autocomplete="email"
                                name="email"
                                placeholder="Email"
                                required
                                type="email"
                            />
                            <InputGroupAddon>
                                <MailIcon />
                            </InputGroupAddon>
                        </InputGroup>
                    </div>
                    <div class="flex flex-col space-y-1.5">
                        <InputGroup>
                            <InputGroupInput
                                :aria-invalid="!!errors.failed"
                                autocomplete="current-password"
                                name="password"
                                placeholder="******"
                                required
                                type="password"
                            />
                            <InputGroupAddon>
                                <Lock />
                            </InputGroupAddon>
                        </InputGroup>
                    </div>
                    <div class="flex items-center gap-3">
                        <Checkbox id="remember" name="remember" value="1" />
                        <Label for="remember">Remember me</Label>
                    </div>
                </div>
            </CardContent>
            <CardFooter class="flex flex-col gap-2">
                <Button :disabled="processing" class="w-full">
                    Login
                    <LogIn />
                </Button>
            </CardFooter>
        </Card>
    </Form>
</template>
