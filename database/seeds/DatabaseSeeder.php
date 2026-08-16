<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable FK checks so we can seed in dependency-agnostic order.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
        $this->call(AdminsTableSeeder::class);
        $this->call(ApiCredentialsTableSeeder::class);
        $this->call(AppVersionTableSeeder::class);
        $this->call(BannersTableSeeder::class);
        $this->call(CancelReasonsTableSeeder::class);
        $this->call(CarTypeTableSeeder::class);
        $this->call(CompanyCorporateAccountsTableSeeder::class);
        $this->call(CompanyCorporateInvoicesTableSeeder::class);
        $this->call(CompanyNetworkPartnersTableSeeder::class);
        $this->call(CompanyPaymentGatewaysTableSeeder::class);
        $this->call(CountryTableSeeder::class);
        $this->call(CurrencyTableSeeder::class);
        $this->call(DocumentsTableSeeder::class);
        $this->call(DriverDocumentsTableSeeder::class);
        $this->call(DriverOnlineSessionsTableSeeder::class);
        $this->call(DriverScoreSettingsTableSeeder::class);
        $this->call(EmailSettingsTableSeeder::class);
        $this->call(FeesTableSeeder::class);
        $this->call(FilterOptionsTableSeeder::class);
        $this->call(GlobalVehicleCategoriesTableSeeder::class);
        $this->call(JoinUsTableSeeder::class);
        $this->call(LanguageTableSeeder::class);
        $this->call(LocationsTableSeeder::class);
        $this->call(ManualBookingDraftsTableSeeder::class);
        $this->call(MetasTableSeeder::class);
        $this->call(NotificationCampaignsTableSeeder::class);
        $this->call(NotificationDispatchLogsTableSeeder::class);
        $this->call(NotificationTemplatesTableSeeder::class);
        $this->call(OperationTeamMembersTableSeeder::class);
        $this->call(OperationTeamRolesTableSeeder::class);
        $this->call(OtpVerificationsTableSeeder::class);
        $this->call(PagesTableSeeder::class);
        $this->call(ParcelCategoriesTableSeeder::class);
        $this->call(ParcelPackageSizesTableSeeder::class);
        $this->call(ParcelSettingsTableSeeder::class);
        $this->call(ParcelTasksTableSeeder::class);
        $this->call(PaymentGatewayTableSeeder::class);
        $this->call(PaymentTransactionsTableSeeder::class);
        $this->call(PayoutCredentialsTableSeeder::class);
        $this->call(PayoutPreferenceTableSeeder::class);
        $this->call(PaytmPaymentsTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(ReferralSettingsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(SaasFeatureModulesTableSeeder::class);
        $this->call(SaasSupportTicketMessagesTableSeeder::class);
        $this->call(SaasSupportTicketsTableSeeder::class);
        $this->call(ScheduleAssignmentAttemptsTableSeeder::class);
        $this->call(SiteSettingsTableSeeder::class);
        $this->call(SupportIssuesTableSeeder::class);
        $this->call(SupportsTableSeeder::class);
        $this->call(TimezoneTableSeeder::class);
        $this->call(TollReasonsTableSeeder::class);
        $this->call(TransactionsTableSeeder::class);
        $this->call(UserLevelAssignmentsTableSeeder::class);
        $this->call(VehicleMakeTableSeeder::class);
        $this->call(VehicleModelTableSeeder::class);
        $this->call(CompaniesTableSeeder::class);
        $this->call(DocumentsLangsTableSeeder::class);
        $this->call(FilterObjectsTableSeeder::class);
        $this->call(FilterOptionsTranslationsTableSeeder::class);
        $this->call(ManageFareTableSeeder::class);
        $this->call(ParcelFaresTableSeeder::class);
        $this->call(PermissionRoleTableSeeder::class);
        $this->call(RoleUserTableSeeder::class);
        $this->call(SupportIssueMessagesTableSeeder::class);
        $this->call(CompanyDispatchSettingsTableSeeder::class);
        $this->call(CompanyDocumentsTableSeeder::class);
        $this->call(CompanyFeeSettingsTableSeeder::class);
        $this->call(CompanySaasSettingsTableSeeder::class);
        $this->call(CompanyServiceAreasTableSeeder::class);
        $this->call(CompanyVehicleTypesTableSeeder::class);
        $this->call(CompanyVerificationOtpsTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CompanyFareRulesTableSeeder::class);
        $this->call(DriverAddressTableSeeder::class);
        $this->call(DriverLocationTableSeeder::class);
        $this->call(DriverOweAmountPaymentsTableSeeder::class);
        $this->call(DriverOweAmountsTableSeeder::class);
        $this->call(DriverScoreAppealsTableSeeder::class);
        $this->call(DriverScoreEventsTableSeeder::class);
        $this->call(DriverScoresTableSeeder::class);
        $this->call(EmergencySosTableSeeder::class);
        $this->call(FavoriteDriversTableSeeder::class);
        $this->call(PaymentMethodTableSeeder::class);
        $this->call(PoolTripsTableSeeder::class);
        $this->call(ProfilePictureTableSeeder::class);
        $this->call(RequestTableSeeder::class);
        $this->call(RiderLocationTableSeeder::class);
        $this->call(ScheduleRideTableSeeder::class);
        $this->call(VehicleTableSeeder::class);
        $this->call(WalletTableSeeder::class);
        $this->call(ParcelRequestsTableSeeder::class);
        $this->call(TripsTableSeeder::class);
        $this->call(CancelTableSeeder::class);
        $this->call(PaymentTableSeeder::class);
        $this->call(RatingTableSeeder::class);
        $this->call(TripTollReasonsTableSeeder::class);
        $this->call(UserLevelsTableSeeder::class);
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
