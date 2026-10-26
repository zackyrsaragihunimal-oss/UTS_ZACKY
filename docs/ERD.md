# ERD Final

```mermaid
erDiagram
 USERS ||--o{ PURCHASES : creates
 USERS ||--o{ SALES : creates
 SUPPLIERS ||--o{ PURCHASES : supplies
 CATEGORIES ||--o{ PRODUCTS : groups
 PRODUCTS ||--o{ PURCHASE_ITEMS : purchased_as
 PRODUCTS ||--o{ SALE_ITEMS : sold_as
 PURCHASES ||--|{ PURCHASE_ITEMS : contains
 SALES ||--|{ SALE_ITEMS : contains
 PRODUCTS ||--o{ STOCK_MOVEMENTS : records
 USERS o|--o{ STOCK_MOVEMENTS : performs
 PRODUCTS ||--o{ PRODUCT_SUPPLIERS : sourced_from
 SUPPLIERS ||--o{ PRODUCT_SUPPLIERS : provides
 USERS { bigint id PK string name string username UK string email UK string password string role }
 CATEGORIES { bigint id PK string name UK }
 SUPPLIERS { bigint id PK string name string phone string email }
 PRODUCTS { bigint id PK bigint category_id FK bigint supplier_id FK string sku UK string name decimal purchase_price decimal selling_price int stock int minimum_stock }
 PURCHASES { bigint id PK bigint supplier_id FK bigint user_id FK string purchase_number UK decimal total string status datetime purchased_at }
 PURCHASE_ITEMS { bigint id PK bigint purchase_id FK bigint product_id FK int quantity decimal unit_cost decimal subtotal }
 SALES { bigint id PK bigint user_id FK string sale_number UK decimal total string status datetime sold_at }
 SALE_ITEMS { bigint id PK bigint sale_id FK bigint product_id FK int quantity decimal unit_price decimal subtotal }
 STOCK_MOVEMENTS { bigint id PK bigint product_id FK bigint user_id FK string type int quantity string note datetime created_at }
 PRODUCT_SUPPLIERS { bigint product_id PK_FK bigint supplier_id PK_FK }
```

Primary keys are surrogate IDs, except composite `(product_id, supplier_id)` on the legacy supplier pivot. Transaction detail rows have individual IDs and foreign keys. Product and user history references use `restrictOnDelete`; transaction detail rows cascade when their header is deleted, but UI has no transaction hard-delete. Monetary values use decimal columns, quantities use unsigned integer columns; request validation enforces positive quantities.
