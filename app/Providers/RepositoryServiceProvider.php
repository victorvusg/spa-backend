<?php

namespace App\Providers;

use App\PaymentMethod;
use App\Repositories\PackageRepository;
use App\Repositories\PackageRepositoryInterface;
use App\Repositories\ComboRepository;
use App\Repositories\ComboRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\CustomerRepositoryInterface;
use App\Repositories\IntakeRepository;
use App\Repositories\IntakeRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\OrderRepositoryInterface;
use App\Repositories\ReviewFormRepository;
use App\Repositories\ReviewFormRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\RoleRepositoryInterface;
use App\Repositories\ServiceCategoryRepository;
use App\Repositories\ServiceCategoryRepositoryInterface;
use App\Repositories\ServiceRepository;
use App\Repositories\ServiceRepositoryInterface;
use App\Repositories\EmployeeRepository;
use App\Repositories\EmployeeRepositoryInterface;
use App\Repositories\StatisticRepository;
use App\Repositories\StatisticRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\VariantRepository;
use App\Repositories\VariantRepositoryInterface;
use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceRepositoryInterface;
use App\Repositories\TaskRepository;
use App\Repositories\TaskRepositoryInterface;
use App\Repositories\TaskAssignmentRepository;
use App\Repositories\TaskAssignmentRepositoryInterface;
use App\Repositories\TaskHistoryRepository;
use App\Repositories\TaskHistoryRepositoryInterface;
use App\Repositories\JudgementRepository;
use App\Repositories\JudgementRepositoryInterface;
use App\Repositories\DiscountRepository;
use App\Repositories\DiscountRepositoryInterface;
use App\Repositories\VariableRepository;
use App\Repositories\VariableRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\PaymentMethodRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);
        $this->app->bind(ServiceCategoryRepositoryInterface::class, ServiceCategoryRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(IntakeRepositoryInterface::class, IntakeRepository::class);
        $this->app->bind(ComboRepositoryInterface::class, ComboRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(ReviewFormRepositoryInterface::class, ReviewFormRepository::class);
        $this->app->bind(VariantRepositoryInterface::class, VariantRepository::class);
        $this->app->bind(StatisticRepositoryInterface::class, StatisticRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(PackageRepositoryInterface::class, PackageRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(TaskAssignmentRepositoryInterface::class, TaskAssignmentRepository::class);
        $this->app->bind(TaskHistoryRepositoryInterface::class, TaskHistoryRepository::class);
        $this->app->bind(JudgementRepositoryInterface::class, JudgementRepository::class);
        $this->app->bind(DiscountRepositoryInterface::class, DiscountRepository::class);
        $this->app->bind(VariableRepositoryInterface::class, VariableRepository::class);
        $this->app->bind(PaymentMethodRepositoryInterface::class, PaymentMethodRepository::class);
    }
}
