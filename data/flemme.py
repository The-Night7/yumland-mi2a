import sqlite3
import tkinter as tk
from tkinter import ttk, filedialog, messagebox
import os

class AppVisualiseurDB(tk.Tk):
    def __init__(self):
        super().__init__()
        self.title("Visualiseur et Schéma de Base de Données SQLite")
        self.geometry("1000x600")
        self.chemin_db = None
        
        self.creer_interface()
        
    def creer_interface(self):
        # --- Barre d'outils supérieure ---
        frame_haut = tk.Frame(self, padx=10, pady=10)
        frame_haut.pack(fill=tk.X)
        
        btn_ouvrir = tk.Button(frame_haut, text="📁 Ouvrir une base de données", command=self.ouvrir_db)
        btn_ouvrir.pack(side=tk.LEFT, padx=5)
        
        btn_test = tk.Button(frame_haut, text="✨ Générer DB de test", command=self.generer_db_test)
        btn_test.pack(side=tk.LEFT, padx=5)
        
        self.label_fichier = tk.Label(frame_haut, text="Aucun fichier sélectionné", fg="gray")
        self.label_fichier.pack(side=tk.LEFT, padx=15)
        
        tk.Label(frame_haut, text="Table :").pack(side=tk.LEFT, padx=(20, 5))
        
        # Liste déroulante pour choisir la table
        self.combo_tables = ttk.Combobox(frame_haut, state="readonly", width=20)
        self.combo_tables.pack(side=tk.LEFT, padx=5)
        self.combo_tables.bind("<<ComboboxSelected>>", self.afficher_table)
        
        btn_rafraichir = tk.Button(frame_haut, text="🔄 Rafraîchir", command=self.rafraichir_donnees)
        btn_rafraichir.pack(side=tk.LEFT, padx=5)

        # --- Système d'onglets (Notebook) ---
        self.notebook = ttk.Notebook(self)
        self.notebook.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        # 1. Onglet Données
        self.tab_donnees = ttk.Frame(self.notebook)
        self.notebook.add(self.tab_donnees, text="📋 Données")
        self.creer_onglet_donnees()
        
        # 2. Onglet Schéma Graphique
        self.tab_schema = ttk.Frame(self.notebook)
        self.notebook.add(self.tab_schema, text="🕸️ Schéma Graphique")
        self.creer_onglet_schema()

    def creer_onglet_donnees(self):
        # Ajout de barres de défilement (Scrollbars)
        scroll_y = ttk.Scrollbar(self.tab_donnees, orient=tk.VERTICAL)
        scroll_x = ttk.Scrollbar(self.tab_donnees, orient=tk.HORIZONTAL)
        
        self.tree = ttk.Treeview(self.tab_donnees, yscrollcommand=scroll_y.set, xscrollcommand=scroll_x.set)
        
        scroll_y.config(command=self.tree.yview)
        scroll_y.pack(side=tk.RIGHT, fill=tk.Y)
        
        scroll_x.config(command=self.tree.xview)
        scroll_x.pack(side=tk.BOTTOM, fill=tk.X)
        
        self.tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        
    def creer_onglet_schema(self):
        # Canvas pour dessiner les tables et les flèches
        self.canvas = tk.Canvas(self.tab_schema, bg="white")
        self.canvas.pack(fill=tk.BOTH, expand=True)
        
    def ouvrir_db(self):
        """Ouvre un explorateur de fichiers pour sélectionner un fichier .db"""
        chemin = filedialog.askopenfilename(
            title="Sélectionner une base de données SQLite",
            filetypes=[("Fichiers SQLite", "*.db *.sqlite *.sqlite3"), ("Tous les fichiers", "*.*")]
        )
        if chemin:
            self.chemin_db = chemin
            self.label_fichier.config(text=os.path.basename(chemin), fg="black")
            self.charger_tables()
            
    def charger_tables(self):
        """Récupère et liste toutes les tables de la base de données sélectionnée"""
        if not self.chemin_db:
            return
            
        try:
            conn = sqlite3.connect(self.chemin_db)
            cursor = conn.cursor()
            cursor.execute("SELECT name FROM sqlite_master WHERE type='table';")
            tables = [row[0] for row in cursor.fetchall() if row[0] != "sqlite_sequence"]
            conn.close()
            
            self.combo_tables['values'] = tables
            if tables:
                self.combo_tables.current(0)
                self.afficher_table()
                self.dessiner_schema() # Met à jour le schéma visuel
            else:
                self.combo_tables.set('')
                self.vider_tableau()
                self.canvas.delete("all")
                messagebox.showinfo("Info", "Aucune table trouvée dans cette base de données.")
                
        except sqlite3.Error as e:
            messagebox.showerror("Erreur", f"Erreur de lecture de la base de données :\n{e}")

    def vider_tableau(self):
        """Nettoie le Treeview (visuel) avant d'afficher de nouvelles données"""
        self.tree.delete(*self.tree.get_children())
        self.tree["columns"] = []
            
    def afficher_table(self, event=None):
        """Affiche le contenu de la table sélectionnée"""
        table_selectionnee = self.combo_tables.get()
        if not self.chemin_db or not table_selectionnee:
            return
            
        self.vider_tableau()
            
        try:
            conn = sqlite3.connect(self.chemin_db)
            cursor = conn.cursor()
            
            cursor.execute(f"PRAGMA table_info('{table_selectionnee}')")
            colonnes = [info[1] for info in cursor.fetchall()]
            
            self.tree["columns"] = colonnes
            self.tree["show"] = "headings"
            
            for col in colonnes:
                self.tree.heading(col, text=col)
                largeur = 150 if "date" in col.lower() or "adresse" in col.lower() else 100
                self.tree.column(col, width=largeur, anchor=tk.CENTER)
                
            cursor.execute(f"SELECT * FROM '{table_selectionnee}'")
            lignes = cursor.fetchall()
            
            for ligne in lignes:
                ligne_formatee = ["NULL" if val is None else val for val in ligne]
                self.tree.insert("", tk.END, values=ligne_formatee)
                
            conn.close()
        except sqlite3.Error as e:
            messagebox.showerror("Erreur SQL", f"Impossible de charger la table :\n{e}")

    def dessiner_schema(self):
        """Dessine les tables, les colonnes et les relations avec un style moderne (Cartes, Courbes)"""
        self.canvas.delete("all")
        # Fond légèrement grisé pour faire ressortir les cartes blanches
        self.canvas.configure(bg="#f3f4f6")
        
        if not self.chemin_db: 
            return
        
        try:
            conn = sqlite3.connect(self.chemin_db)
            cursor = conn.cursor()
            
            cursor.execute("SELECT name FROM sqlite_master WHERE type='table';")
            tables = [row[0] for row in cursor.fetchall() if row[0] != "sqlite_sequence"]
            
            # --- Étape 1 : Calculer les positions et dimensions ---
            x, y = 50, 50
            max_h_row = 0
            mises_en_page = {}
            
            for table in tables:
                cursor.execute(f"PRAGMA table_info('{table}')")
                cols = cursor.fetchall()
                
                width = 280
                header_height = 40
                row_height = 25
                height = header_height + len(cols) * row_height
                max_h_row = max(max_h_row, height)
                
                mises_en_page[table] = {
                    "x": x, "y": y, "w": width, "h": height,
                    "cx": x + width/2, "cy": y + height/2,
                    "cols_info": cols,
                    "cols_y": {} # Pour stocker la position Y exacte de chaque colonne (pour les flèches)
                }
                
                # Enregistrer la position Y de chaque colonne
                cy = y + header_height
                for col in cols:
                    mises_en_page[table]["cols_y"][col[1]] = cy + row_height/2
                    cy += row_height
                
                # Placer la table suivante
                x += 350
                if x > 750: # Retour à la ligne en fonction de la table la plus haute
                    x = 50
                    y += max_h_row + 60
                    max_h_row = 0
            
            # --- Étape 2 : Dessiner les relations (Flèches courbes de Bézier) ---
            liens_multiples = {} # Dictionnaire pour compter les liens entre deux mêmes tables
            
            for table in tables:
                cursor.execute(f"PRAGMA foreign_key_list('{table}')")
                fks = cursor.fetchall()
                for fk in fks:
                    cible = fk[2]
                    if table in mises_en_page and cible in mises_en_page:
                        src = mises_en_page[table]
                        dst = mises_en_page[cible]
                        col_source = fk[3]
                        col_cible = fk[4] # Nom de la colonne dans la table de destination
                        
                        paire = (table, cible)
                        idx_lien = liens_multiples.get(paire, 0)
                        liens_multiples[paire] = idx_lien + 1
                        
                        # Si la colonne cible n'est pas spécifiée explicitement dans la définition 
                        # de la clé étrangère, on cherche la clé primaire de la table cible.
                        if not col_cible:
                            for c in dst["cols_info"]:
                                if c[5]: # c[5] vaut 1 si la colonne est une Primary Key
                                    col_cible = c[1]
                                    break
                        
                        # Point de départ : bord de la table source, au niveau de la colonne FK
                        x1 = src["x"] + src["w"]
                        y1 = src["cols_y"].get(col_source, src["cy"])
                        
                        # Point d'arrivée : bord de la table cible, au niveau de la colonne d'origine
                        x2 = dst["x"]
                        y2 = dst["cols_y"].get(col_cible, dst["cy"])
                        
                        # Léger décalage vertical à l'arrivée si plusieurs flèches visent la même cible
                        y2 += (idx_lien * 12)
                        
                        # Si la table cible est à gauche, on inverse l'attachement pour que ça soit propre
                        if dst["x"] < src["x"]:
                            x1 = src["x"]
                            x2 = dst["x"] + dst["w"]
                        
                        # Calcul des points de contrôle pour une jolie courbe douce (Bézier)
                        offset = abs(x2 - x1) / 2
                        if offset < 50: offset = 50
                        
                        cx1 = x1 + offset if x1 < x2 else x1 - offset
                        cx2 = x2 - offset if x1 < x2 else x2 + offset
                        cy1 = y1
                        cy2 = y2
                        
                        # Écarter physiquement les courbes s'il y a plusieurs liens entre les mêmes tables
                        if idx_lien > 0:
                            direction = -1 if idx_lien % 2 == 1 else 1
                            amplitude = 60 * ((idx_lien + 1) // 2)
                            cy1 += direction * amplitude
                            cy2 += direction * amplitude
                        
                        # Dessin de la ligne courbe rose/magenta avec les nouveaux points de contrôle
                        self.canvas.create_line(x1, y1, cx1, cy1, cx2, cy2, x2, y2, 
                                                smooth=True, arrow=tk.LAST, fill="#ec4899", width=2.5)
                        
                        # Calcul mathématique du point central d'une courbe de Bézier cubique (t=0.5)
                        # Cela permet de placer le badge EXACTEMENT sur la nouvelle trajectoire de la courbe écartée
                        mx = 0.125*x1 + 0.375*cx1 + 0.375*cx2 + 0.125*x2
                        my = 0.125*y1 + 0.375*cy1 + 0.375*cy2 + 0.125*y2
                        
                        # Petit badge sur la flèche
                        self.canvas.create_rectangle(mx-35, my-10, mx+35, my+10, fill="#fdf2f8", outline="#fbcfe8", width=1)
                        self.canvas.create_text(mx, my, text=f"🔑 {col_source}", fill="#be185d", font=("Helvetica", 8, "bold"))

            # --- Étape 3 : Dessiner les boîtes (Cartes modernes) ---
            for table, layout in mises_en_page.items():
                tx, ty, tw, th = layout["x"], layout["y"], layout["w"], layout["h"]
                header_h = 40
                
                # Ombre douce (empilement de rectangles)
                self.canvas.create_rectangle(tx+3, ty+3, tx+tw+3, ty+th+3, fill="#e5e7eb", outline="")
                self.canvas.create_rectangle(tx+6, ty+6, tx+tw+6, ty+th+6, fill="#d1d5db", outline="")
                
                # Fond de la carte principal
                self.canvas.create_rectangle(tx, ty, tx+tw, ty+th, fill="#ffffff", outline="#d1d5db", width=1)
                
                # En-tête de la table (Indigo)
                self.canvas.create_rectangle(tx, ty, tx+tw, ty+header_h, fill="#4f46e5", outline="#4f46e5")
                self.canvas.create_text(tx + 15, ty + header_h/2, text=table.upper(), anchor=tk.W, fill="#ffffff", font=("Helvetica", 11, "bold"))
                self.canvas.create_text(tx + tw - 15, ty + header_h/2, text="📋", anchor=tk.E, fill="#ffffff", font=("Helvetica", 12))
                
                # Affichage des lignes de colonnes
                cy = ty + header_h
                for i, col in enumerate(layout["cols_info"]):
                    nom_col, type_col, pk = col[1], col[2], col[5]
                    
                    # Zébrage subtil (lignes alternées)
                    if i % 2 == 0:
                        self.canvas.create_rectangle(tx+1, cy, tx+tw-1, cy+25, fill="#f9fafb", outline="")
                    
                    # Icône et Nom de la colonne à gauche
                    if pk:
                        self.canvas.create_text(tx + 12, cy + 12.5, text="🔑", anchor=tk.W, font=("Helvetica", 10))
                        self.canvas.create_text(tx + 35, cy + 12.5, text=nom_col, anchor=tk.W, font=("Helvetica", 9, "bold"), fill="#111827")
                    else:
                        self.canvas.create_text(tx + 15, cy + 12.5, text="•", anchor=tk.W, font=("Helvetica", 10), fill="#9ca3af")
                        self.canvas.create_text(tx + 30, cy + 12.5, text=nom_col, anchor=tk.W, font=("Helvetica", 9), fill="#374151")
                    
                    # Type de la donnée aligné à l'extrême droite
                    self.canvas.create_text(tx + tw - 15, cy + 12.5, text=type_col, anchor=tk.E, font=("Courier", 8, "italic"), fill="#6b7280")
                    
                    cy += 25
                    
            conn.close()
        except sqlite3.Error as e:
            messagebox.showerror("Erreur SQL", f"Impossible de générer le schéma :\n{e}")

    def rafraichir_donnees(self):
        self.afficher_table()
        self.dessiner_schema()
        
    def generer_db_test(self):
        """Crée une base de données avec ton schéma exact pour faire des tests"""
        chemin = filedialog.asksaveasfilename(
            defaultextension=".db",
            initialfile="ma_base_test.db",
            title="Enregistrer la base de données de test sous...",
            filetypes=[("Fichiers SQLite", "*.db")]
        )
        if not chemin:
            return
            
        try:
            conn = sqlite3.connect(chemin)
            cursor = conn.cursor()
            
            cursor.execute('''
            CREATE TABLE Utilisateurs (
                id_user      INTEGER PRIMARY KEY AUTOINCREMENT,
                nom          TEXT    NOT NULL,
                email        TEXT    NOT NULL UNIQUE,
                mot_de_passe TEXT    NOT NULL,
                role         TEXT    NOT NULL CHECK (role IN ('Client', 'Administrateur', 'Restaurateur', 'Livreur') ),
                solde_miams  INTEGER DEFAULT 0
            );
            ''')
            
            cursor.execute('''
            CREATE TABLE Commandes (
                id_commande       INTEGER  PRIMARY KEY AUTOINCREMENT,
                id_client         INTEGER  NOT NULL,
                id_livreur        INTEGER,
                statut            TEXT     DEFAULT 'En attente' CHECK (statut IN ('En attente', 'En préparation', 'En livraison', 'Livrée') ),
                prix_total        REAL     NOT NULL,
                adresse_livraison TEXT     NOT NULL,
                code_interphone   TEXT,
                date_commande     DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_client) REFERENCES Utilisateurs (id_user),
                FOREIGN KEY (id_livreur) REFERENCES Utilisateurs (id_user) 
            );
            ''')
            
            cursor.execute("INSERT INTO Utilisateurs (nom, email, mot_de_passe, role, solde_miams) VALUES ('Alice Martin', 'alice@email.com', 'pass123', 'Client', 150)")
            cursor.execute("INSERT INTO Utilisateurs (nom, email, mot_de_passe, role) VALUES ('Bob LeLivreur', 'bob@email.com', 'pass456', 'Livreur')")
            cursor.execute("INSERT INTO Utilisateurs (nom, email, mot_de_passe, role) VALUES ('Charlie', 'charlie@email.com', 'admin789', 'Administrateur')")
            
            cursor.execute("INSERT INTO Commandes (id_client, id_livreur, statut, prix_total, adresse_livraison, code_interphone) VALUES (1, 2, 'En livraison', 25.50, '123 Rue de la Soif, Paris', '12A')")
            cursor.execute("INSERT INTO Commandes (id_client, statut, prix_total, adresse_livraison, code_interphone) VALUES (1, 'En préparation', 14.90, '123 Rue de la Soif, Paris', '12A')")
            
            conn.commit()
            conn.close()
            
            self.chemin_db = chemin
            self.label_fichier.config(text=os.path.basename(chemin), fg="black")
            self.charger_tables()
            messagebox.showinfo("Succès", "Base de données de test créée avec succès avec quelques fausses données !")
            
        except sqlite3.Error as e:
            messagebox.showerror("Erreur", f"Erreur lors de la création de la base de test :\n{e}")

if __name__ == "__main__":
    app = AppVisualiseurDB()
    app.mainloop()